# ==============================================================================
# Script Cek Status Layanan Chatbot AI - Sistem Informasi Fasilitas STMIK IKMI
# Menampilkan kondisi proses, port 3001, dan koneksi database MySQL
# ==============================================================================

$ErrorActionPreference = "Continue"

$ServiceDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$LogsDir    = Join-Path $ServiceDir "logs"
$PidFile    = Join-Path $LogsDir "chatbot.pid"

Write-Host "`n=== STATUS LAYANAN CHATBOT AI STMIK IKMI CIREBON ===" -ForegroundColor Cyan

$healthUrl = "http://127.0.0.1:3001/api/health"

try {
    $resp = Invoke-RestMethod -Uri $healthUrl -Method Get -TimeoutSec 3 -ErrorAction Stop

    Write-Host "[STATUS]        : AKTIF (Running & Healthy)" -ForegroundColor Green
    Write-Host "[NAMA LAYANAN]  : $($resp.service)"
    Write-Host "[PORT AKTIF]    : $($resp.port)"
    
    if ($resp.database -eq 'connected') {
        Write-Host "[KONEKSI MYSQL] : TERHUBUNG (Connected)" -ForegroundColor Green
    } else {
        Write-Host "[KONEKSI MYSQL] : TERPUTUS (Disconnected / Laragon belum jalan)" -ForegroundColor Yellow
    }

    if ($resp.stats) {
        Write-Host "[DATA AKTUAL]   : $($resp.stats.gedung) Gedung, $($resp.stats.ruangan) Ruangan, $($resp.stats.fasilitas_item) Jenis Fasilitas ($($resp.stats.fasilitas_unit) Unit)"
    }

    if (Test-Path $PidFile) {
        $pidVal = (Get-Content $PidFile -Raw -ErrorAction SilentlyContinue).Trim()
        Write-Host "[PROCESS ID]    : $pidVal"
    }

    Write-Host "[ENDPOINT CHAT] : http://localhost:$($resp.port)/api/chat`n"

} catch {
    Write-Host "[STATUS]        : NONAKTIF / TIDAK BERJALAN" -ForegroundColor Red
    Write-Host "[KETERANGAN]    : Tidak ada respons dari $healthUrl"
    
    if (Test-Path $PidFile) {
        $pidVal = (Get-Content $PidFile -Raw -ErrorAction SilentlyContinue).Trim()
        Write-Host "[PID TERCATAT]  : $pidVal (Proses kemungkinan telah terhenti)"
    }
    Write-Host "[SOLUSI]        : Jalankan 'start_service.bat' atau 'enable_startup.bat'.`n"
}
