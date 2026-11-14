@echo off
:: ==============================================================================
:: Skrip Penghapusan Windows Task Scheduler (Memerlukan Hak Administrator)
:: ==============================================================================
title Hapus Task Scheduler Chatbot AI STMIK IKMI
cd /d "%~dp0"

echo Memeriksa hak Administrator...
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [PERINGATAN] Skrip ini membutuhkan hak Administrator untuk menghapus dari Task Scheduler!
    echo Silakan klik kanan file ini dan pilih "Run as administrator".
    echo.
    pause
    exit /b 1
)

set TASK_NAME=STMIK_IKMI_Chatbot_Service

schtasks /Delete /TN "%TASK_NAME%" /F

if %errorlevel% equ 0 (
    echo.
    echo [SUKSES] Tugas '%TASK_NAME%' berhasil dihapus dari Windows Task Scheduler!
) else (
    echo.
    echo [INFO] Tugas '%TASK_NAME%' mungkin memang belum terdaftar di Task Scheduler.
)

echo.
pause
