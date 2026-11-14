@echo off
title Menjalankan Chatbot AI STMIK IKMI
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0start_service.ps1"
echo.
pause
