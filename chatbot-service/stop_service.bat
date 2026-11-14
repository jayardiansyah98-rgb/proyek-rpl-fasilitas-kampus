@echo off
title Menghentikan Chatbot AI STMIK IKMI
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0stop_service.ps1"
echo.
pause
