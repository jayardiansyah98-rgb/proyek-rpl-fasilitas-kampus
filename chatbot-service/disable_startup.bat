@echo off
title Nonaktivasi Startup Otomatis Chatbot AI STMIK IKMI
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0disable_startup.ps1"
echo.
pause
