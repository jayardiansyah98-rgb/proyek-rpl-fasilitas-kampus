@echo off
:: ==============================================================================
:: Skrip Pendaftaran Windows Task Scheduler (Memerlukan Hak Administrator)
:: Menjalankan run_silent.vbs saat user login ke Windows
:: ==============================================================================
title Registrasi Task Scheduler Chatbot AI STMIK IKMI
cd /d "%~dp0"

echo Memeriksa hak Administrator...
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [PERINGATAN] Skrip ini membutuhkan hak Administrator untuk mendaftar ke Task Scheduler!
    echo Silakan klik kanan file ini dan pilih "Run as administrator".
    echo.
    echo TIPS: Jika ingin mengaktifkan startup TANPA hak Administrator,
    echo gunakan cukup jalankan file "enable_startup.bat" (Sangat direkomendasikan).
    echo.
    pause
    exit /b 1
)

set TASK_NAME=STMIK_IKMI_Chatbot_Service
set VBS_PATH=%~dp0run_silent.vbs

echo Mendaftarkan tugas ke Windows Task Scheduler...
schtasks /Create /TN "%TASK_NAME%" /TR "wscript.exe \"%VBS_PATH%\"" /SC ONLOGON /F

if %errorlevel% equ 0 (
    echo.
    echo [SUKSES] Tugas '%TASK_NAME%' berhasil didaftarkan ke Windows Task Scheduler!
    echo Chatbot akan otomatis berjalan di latar belakang setiap kali login.
) else (
    echo.
    echo [GAGAL] Terjadi kesalahan saat mendaftarkan ke Task Scheduler.
)

echo.
pause
