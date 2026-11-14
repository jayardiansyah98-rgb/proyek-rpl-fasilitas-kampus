@echo off
title Cek Status Chatbot AI STMIK IKMI
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0status_service.ps1"
echo.
pause
