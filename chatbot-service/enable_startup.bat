@echo off
title Aktivasi Startup Otomatis Chatbot AI STMIK IKMI
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0enable_startup.ps1"
echo.
pause
