# ==============================================================================
# Script Aktivasi Startup Otomatis - Chatbot AI STMIK IKMI Cirebon
# Mendaftarkan chatbot agar otomatis aktif saat pengguna login ke Windows
# (Aman, tanpa memerlukan hak Administrator atau merusak pengaturan keamanan)
# ==============================================================================

$ErrorActionPreference = "Continue"

$ServiceDir    = Split-Path -Parent $MyInvocation.MyCommand.Path
$VbsScript     = Join-Path $ServiceDir "run_silent.vbs"
$StartupFolder = [Environment]::GetFolderPath("Startup")
$ShortcutPath  = Join-Path $StartupFolder "STMIK_IKMI_Chatbot.lnk"

Write-Host "`n=== AKTIVASI STARTUP OTOMATIS CHATBOT AI STMIK IKMI ===" -ForegroundColor Cyan

if (-not (Test-Path $VbsScript)) {
    Write-Host "[ERROR] File '$VbsScript' tidak ditemukan!" -ForegroundColor Red
    exit 1
}

try {
    # Buat shortcut .lnk pada folder Startup pengguna saat ini
    $wsh = New-Object -ComObject WScript.Shell
    $shortcut = $wsh.CreateShortcut($ShortcutPath)
    $shortcut.TargetPath       = "wscript.exe"
    $shortcut.Arguments        = "`"$VbsScript`""
    $shortcut.WorkingDirectory = $ServiceDir
    $shortcut.Description      = "Background Service Chatbot AI STMIK IKMI Cirebon"
    $shortcut.WindowStyle      = 7 # Minimized / Silent
    $shortcut.Save()

    Write-Host "[SUKSES] Startup otomatis berhasil diaktifkan!" -ForegroundColor Green
    Write-Host "[LOKASI] : $ShortcutPath"
    Write-Host "[TARGET] : wscript.exe `"$VbsScript`""
    Write-Host "[INFO]   : Chatbot akan otomatis berjalan di background setiap kali Anda login ke Windows."
    Write-Host "           Tidak ada jendela konsol hitam (CMD/PowerShell) yang akan muncul mengganggu.`n"

    # Jalankan layanan sekarang jika belum berjalan
    $startScript = Join-Path $ServiceDir "start_service.ps1"
    Write-Host "Menjalankan pengecekan layanan saat ini..." -ForegroundColor Yellow
    & powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$startScript"

} catch {
    Write-Host "[ERROR] Gagal membuat shortcut startup: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
