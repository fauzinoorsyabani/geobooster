@echo off
title ZiuLabs Server & Cloudflare Tunnel Runner
color 0b

echo ================================================================
echo             ZIULABS - ONE-CLICK AUTO LAUNCHER
echo ================================================================
echo.

:: 1. Cek & Jalankan MySQL jika belum jalan
tasklist /fi "imagename eq mysqld.exe" 2>NUL | find /i /n "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Database MySQL sudah berjalan.
) else (
    echo [..] Menyalakan Database MySQL...
    start "" /B "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" --defaults-file="C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini"
    timeout /t 2 /nobreak >NUL
    echo [OK] Database MySQL berhasil dinyalakan.
)

:: 2. Cek & Jalankan PHP Web Server (Port 8080)
netstat -ano | findstr /R /C:":8080 .*LISTENING" >NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Server PHP (Port 8080) sudah berjalan.
) else (
    echo [..] Menyalakan PHP Server (Port 8080)...
    start /min "ZiuLabs-PHP" "C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe" -S 127.0.0.1:8080 -t "C:\laragon\www\ziulabs\php-app\public"
    timeout /t 2 /nobreak >NUL
    echo [OK] Server PHP berhasil dinyalakan.
)

echo.
echo ================================================================
echo  STATUS SISTEM:
echo   - Domain Publik : https://www.ziulabs.biz.id
echo   - Lokal Laptop  : http://127.0.0.1:8080
echo.
echo  Menghubungkan Cloudflare Tunnel resmi ziulabs.biz.id...
echo  (JANGAN TUTUP JENDELA INI AGAR WEBSITE ANDA TETAP ONLINE)
echo ================================================================
echo.

:: 3. Jalankan Cloudflare Tunnel di jendela utama
"C:\laragon\bin\cloudflared\cloudflared.exe" tunnel --config "C:\Users\HP\.cloudflared\config.yml" run ziulabs

pause
