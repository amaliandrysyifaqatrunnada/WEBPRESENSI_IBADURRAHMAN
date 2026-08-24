@echo off
title Memulai Sistem Presensi PKBM Ibadurrahman
echo ==================================================
echo   MEMULAI SISTEM PRESENSI PKBM IBADURRAHMAN
echo ==================================================
echo.

:: 1. Berpindah ke folder project C:\ibadurrahman
cd /d C:\ibadurrahman

:: 2. Menjalankan Laravel Serve di window baru
echo [1/2] Menjalankan Server Laravel (Port 8000)...
start "Laravel Server" cmd /k "C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000"

:: 3. Menunggu 3 detik agar server menyala sempurna
timeout /t 3 /nobreak >nul

:: 4. Menjalankan SSH Tunnel di window ini agar link langsung tampil ke user
echo [2/2] Menghubungkan Tunnel ke HP (localhost.run)...
echo.
echo Alamat URL untuk HP akan muncul di bawah ini (biasanya berakhiran .lhr.life)
echo -----------------------------------------------------------------------------------------
ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 -o ServerAliveInterval=10 -o ServerAliveCountMax=2 -R 80:127.0.0.1:8000 nokey@localhost.run

pause
