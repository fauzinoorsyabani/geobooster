<?php

declare(strict_types=1);

return [
    'name' => 'ZiuLabs',
    'tagline' => 'Digital Products for a Smarter You',
    'currency' => 'IDR',
    'support_url' => 'https://wa.me/62895609250509',
    'whatsapp_number' => '+62895609250509',
    'environment' => getenv('APP_ENV') ?: 'local',
    'clerk_publishable_key' => getenv('CLERK_PUBLISHABLE_KEY') ?: 'pk_test_dW5pZmllZC1tb2xlLTI1LmNsZXJrLmFjY291bnRzLmRldiQ',
    'clerk_frontend_api' => getenv('CLERK_FRONTEND_API') ?: 'https://unified-mole-25.clerk.accounts.dev',
];

