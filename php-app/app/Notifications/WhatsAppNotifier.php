<?php

declare(strict_types=1);

namespace GeoBooster\Notifications;

use PDO;
use RuntimeException;
use Throwable;
use GeoBooster\Security\AuditLogger;

final class WhatsAppNotifier
{
    private string $provider;
    private string $apiKey;
    private string $apiUrl;
    private string $senderPhone;
    private string $appUrl;

    public function __construct(private readonly PDO $db)
    {
        $this->provider = strtolower((string) (getenv('WA_PROVIDER') ?: 'mock'));
        $this->apiKey = (string) (getenv('WA_API_KEY') ?: '');
        $this->apiUrl = (string) (getenv('WA_API_URL') ?: 'https://api.fonnte.com/send');
        $this->senderPhone = (string) (getenv('WA_SENDER_PHONE') ?: '62895609250509');
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '' && !str_starts_with($host, '127.0.0.1') && !str_starts_with($host, 'localhost')) {
            $proto = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $this->appUrl = $proto . '://' . $host;
        } else {
            $this->appUrl = rtrim((string) (getenv('APP_URL') ?: 'http://127.0.0.1:8080'), '/');
        }
    }

    /**
     * Normalize Indonesian phone number into 628... format
     */
    public static function normalizePhone(string|int|null $rawPhone): string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) ($rawPhone ?? '')) ?: '';
        if (str_starts_with($clean, '08')) {
            return '628' . substr($clean, 2);
        }
        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }
        return $clean;
    }

    /**
     * Generate direct wa.me link with encoded text
     */
    public static function buildDirectChatUrl(string $phone, string $message): string
    {
        $cleanPhone = self::normalizePhone($phone);
        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
    }

    /**
     * Build invoice message template
     */
    public function formatInvoiceMessage(array $order, string $productName): string
    {
        $name = htmlspecialchars_decode((string) ($order['customer_name'] ?? 'Pelanggan'), ENT_QUOTES);
        $publicId = (string) ($order['public_id'] ?? '');
        $totalMinor = (int) ($order['total_minor'] ?? 0);
        $totalFormatted = 'Rp' . number_format($totalMinor / 100, 0, ',', '.');
        $paymentUrl = $this->appUrl . '/order/payment.php?id=' . urlencode($publicId);
        $csPhone = $this->senderPhone;

        return "Halo *{$name}*! 👋\n\n"
             . "Terima kasih telah memesan di *ZiuLabs*.\n"
             . "Pesanan Anda berhasil dibuat dan menunggu pembayaran:\n\n"
             . "📋 *RINCIAN PESANAN*\n"
             . "• ID Pesanan: `{$publicId}`\n"
             . "• Produk: *{$productName}*\n"
             . "• Total Tagihan: *{$totalFormatted}*\n"
             . "• Batas Waktu: 30 Menit\n\n"
             . "💳 *LINK PEMBAYARAN QRIS / TRANSFER:*\n"
             . "👉 {$paymentUrl}\n\n"
             . "⚡ *Catatan:* Setelah pembayaran berhasil, akun & lisensi digital Anda akan otomatis dikirim instan ke WhatsApp ini dan muncul di layar.\n\n"
             . "🛡️ Butuh bantuan? Chat Customer Support ZiuLabs:\n"
             . "https://wa.me/{$csPhone}";
    }

    /**
     * Build fulfillment & credentials delivery message template
     */
    public function formatFulfillmentMessage(array $order, array $items): string
    {
        $name = htmlspecialchars_decode((string) ($order['customer_name'] ?? 'Pelanggan'), ENT_QUOTES);
        $publicId = (string) ($order['public_id'] ?? '');
        $dashboardUrl = $this->appUrl . '/dashboard/';
        $csPhone = $this->senderPhone;

        $credLines = [];
        foreach ($items as $idx => $item) {
            $num = $idx + 1;
            $skuName = (string) ($item['sku_name'] ?? 'Produk Digital');
            $line = "📦 *Item #{$num}: {$skuName}*";

            if (!empty($item['account_email'])) {
                $line .= "\n   📧 Email / ID: `{$item['account_email']}`";
            }
            if (!empty($item['account_password'])) {
                $line .= "\n   🔑 Password: `{$item['account_password']}`";
            }
            if (!empty($item['license_key'])) {
                $line .= "\n   🔑 Lisensi: `{$item['license_key']}`";
            }
            if (!empty($item['invite_url'])) {
                $line .= "\n   🔗 Link Aktivasi: {$item['invite_url']}";
            }
            if (!empty($item['additional_notes'])) {
                $line .= "\n   💡 Info: {$item['additional_notes']}";
            }
            $credLines[] = $line;
        }

        $credentialsBlock = implode("\n\n", $credLines);

        return "Halo *{$name}*! 🎉\n\n"
             . "Pembayaran untuk pesanan `{$publicId}` telah *TERKONFIRMASI (LUNAS)*!\n"
             . "Aset digital Anda telah siap digunakan:\n\n"
             . "══════════════════════\n"
             . "🔐 *AKUN & KREDENSIAL LISENSI*\n"
             . "══════════════════════\n"
             . "{$credentialsBlock}\n\n"
             . "══════════════════════\n"
             . "🛡️ *GARANSI TOKO 30 HARI*\n"
             . "• Akun ini dilindungi Garansi Resmi Toko ZiuLabs 30 Hari.\n"
             . "• Dilarang mengganti email/password profil utama tanpa panduan agar garansi tetap aktif.\n"
             . "• Pantau masa aktif & pesanan Anda di Customer Portal:\n"
             . "👉 {$dashboardUrl}\n\n"
             . "Ada kendala login? Hubungi CS kami dengan menyebutkan ID Pesanan `{$publicId}`:\n"
             . "👉 https://wa.me/{$csPhone}\n\n"
             . "Terima kasih telah berbelanja di ZiuLabs! 🚀";
    }

    /**
     * Send order invoice / awaiting payment notification
     */
    public function sendInvoiceNotification(array $order, string $productName): array
    {
        $phone = self::normalizePhone((string) ($order['customer_phone'] ?? ''));
        $msg = $this->formatInvoiceMessage($order, $productName);

        return $this->dispatch((int) ($order['id'] ?? 0), $phone, $msg, 'order_invoice');
    }

    /**
     * Send fulfillment / credentials delivery notification
     */
    public function sendFulfillmentNotification(array $order, array $items): array
    {
        $phone = self::normalizePhone((string) ($order['customer_phone'] ?? ''));
        $msg = $this->formatFulfillmentMessage($order, $items);

        return $this->dispatch((int) ($order['id'] ?? 0), $phone, $msg, 'order_fulfillment');
    }

    /**
     * Dispatch message through provider gateway with audit logging & fallback URL
     */
    private function dispatch(int $orderId, string $phone, string $message, string $notificationType): array
    {
        $directUrl = self::buildDirectChatUrl($phone, $message);
        $result = [
            'success' => false,
            'provider' => $this->provider,
            'phone' => $phone,
            'direct_url' => $directUrl,
            'error' => null,
            'response_payload' => null,
        ];

        if ($phone === '') {
            $result['error'] = 'Nomor WhatsApp kosong.';
            $this->logAudit($orderId, 'notification_failed', $result);
            return $result;
        }

        if ($this->provider === 'mock' || $this->provider === '') {
            // Mock mode for local testing without depleting SMS/WA credit
            $result['success'] = true;
            $result['response_payload'] = ['status' => 'simulated', 'timestamp' => time()];
            $this->logAudit($orderId, 'notification_sent', [
                'type' => $notificationType,
                'channel' => 'whatsapp',
                'provider' => 'mock',
                'phone' => $phone,
                'status' => 'delivered_mock',
                'direct_url' => $directUrl,
            ]);
            return $result;
        }

        if ($this->provider === 'fonnte') {
            try {
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $this->apiUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => http_build_query([
                        'target' => $phone,
                        'message' => $message,
                        'countryCode' => '62',
                    ]),
                    CURLOPT_HTTPHEADER => [
                        'Authorization: ' . $this->apiKey,
                    ],
                ]);
                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($curl);
                curl_close($curl);

                if ($curlErr) {
                    throw new RuntimeException("cURL Error: " . $curlErr);
                }

                $decoded = json_decode((string) $response, true);
                if ($httpCode >= 200 && $httpCode < 300 && (!isset($decoded['status']) || $decoded['status'] !== false)) {
                    $result['success'] = true;
                    $result['response_payload'] = $decoded;
                    $this->logAudit($orderId, 'notification_sent', [
                        'type' => $notificationType,
                        'channel' => 'whatsapp',
                        'provider' => 'fonnte',
                        'phone' => $phone,
                        'response' => $decoded,
                    ]);
                } else {
                    $result['error'] = 'Fonnte HTTP ' . $httpCode . ': ' . ($response ?: 'empty response');
                    $result['response_payload'] = $decoded;
                    $this->logAudit($orderId, 'notification_failed', [
                        'type' => $notificationType,
                        'channel' => 'whatsapp',
                        'provider' => 'fonnte',
                        'phone' => $phone,
                        'error' => $result['error'],
                    ]);
                }
            } catch (Throwable $e) {
                $result['error'] = $e->getMessage();
                $this->logAudit($orderId, 'notification_failed', [
                    'type' => $notificationType,
                    'channel' => 'whatsapp',
                    'provider' => 'fonnte',
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }

            return $result;
        }

        // Generic webhook provider fallback
        try {
            $payload = json_encode([
                'order_id' => $orderId,
                'phone' => $phone,
                'message' => $message,
                'type' => $notificationType,
            ]);

            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n" .
                                ($this->apiKey !== '' ? "Authorization: Bearer {$this->apiKey}\r\n" : ''),
                    'content' => $payload,
                    'timeout' => 10,
                ],
            ];
            $context = stream_context_create($opts);
            $res = @file_get_contents($this->apiUrl, false, $context);
            if ($res !== false) {
                $result['success'] = true;
                $result['response_payload'] = json_decode($res, true) ?: $res;
                $this->logAudit($orderId, 'notification_sent', [
                    'type' => $notificationType,
                    'channel' => 'whatsapp',
                    'provider' => $this->provider,
                    'phone' => $phone,
                ]);
            } else {
                $result['error'] = 'Failed to connect to WhatsApp webhook endpoint: ' . $this->apiUrl;
                $this->logAudit($orderId, 'notification_failed', [
                    'type' => $notificationType,
                    'channel' => 'whatsapp',
                    'provider' => $this->provider,
                    'phone' => $phone,
                    'error' => $result['error'],
                ]);
            }
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
            $this->logAudit($orderId, 'notification_failed', [
                'type' => $notificationType,
                'channel' => 'whatsapp',
                'provider' => $this->provider,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    private function logAudit(int $orderId, string $action, array $meta): void
    {
        try {
            (new AuditLogger($this->db))->record(null, $action, 'order', $orderId > 0 ? $orderId : null, $meta);
        } catch (Throwable) {
            // Ignore audit log failure during notification
        }
    }
}
