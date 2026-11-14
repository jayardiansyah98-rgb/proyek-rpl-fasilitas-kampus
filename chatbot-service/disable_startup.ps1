# ==============================================================================
# Script Nonaktivasi Startup Otomatis - Chatbot AI STMIK IKMI Cirebon
# Menghapus pendaftaran startup otomatis dan menghentikan layanan jika diinginkan
# ==============================================================================

$ErrorActionPreference = "Continue"

$ServiceDir    = Split-Path -Parent $MyInvocation.MyCommand.Path
$StartupFolder = [Environment]::GetFolderPath("Startup")
$ShortcutPath  = Join-Path $StartupFolder "STMIK_IKMI_Chatbot.lnk"

Write-Host "`n=== NONAKTIVASI STARTUP OTOMATIS CHATBOT AI STMIK IKMI ===" -ForegroundColor Cyan

$removed = $false

# 1. Hapus dari folder Startup
if (Test-Path $ShortcutPath) {
    Remove-Item -Path $ShortcutPath -Force -ErrorAction SilentlyContinue
    Write-Host "[SUKSES] Shortcut startup otomatis telah dihapus dari:" -ForegroundColor Green
    Write-Host "         $ShortcutPath"
    $removed = $true
} else {
    Write-Host "[INFO] Shortcut startup tidak ditemukan di folder Startup pengguna."
}

# 2. Hapus dari Task Scheduler jika sebelumnya pernah didaftarkan
try {
    $task = Get-ScheduledTask -TaskName "STMIK_IKMI_Chatbot_Service" -ErrorAction SilentlyContinue
    if ($task) {
        Unregister-ScheduledTask -TaskName "STMIK_IKMI_Chatbot_Service" -Confirm:$false -ErrorAction SilentlyContinue
        Write-Host "[INFO] Jadwal tugas 'STMIK_IKMI_Chatbot_Service' di Task Scheduler telah dihapus."
        $removed = $true
    }
} catch {
    # Task scheduler query might fail if not elevated, safe to ignore
}

# 3. Hentikan layanan yang saat ini berjalan
$stopScript = Join-Path $ServiceDir "stop_service.ps1"
if (Test-Path $stopScript) {
    Write-Host "`nMenghentikan layanan chatbot yang sedang aktif..." -ForegroundColor Yellow
    & powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$stopScript"
}

Write-Host "`n[SELESAI] Chatbot tidak akan berjalan otomatis lagi saat login Windows.`n"
