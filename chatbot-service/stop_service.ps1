# ==============================================================================
# Script Stop Layanan Chatbot AI - Sistem Informasi Fasilitas STMIK IKMI
# Menghentikan HANYA proses chatbot pada port 3001 secara presisi tanpa mematikan proses lain
# ==============================================================================

$ErrorActionPreference = "Continue"

$ServiceDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$LogsDir    = Join-Path $ServiceDir "logs"
$StartupLog = Join-Path $LogsDir "startup.log"
$PidFile    = Join-Path $LogsDir "chatbot.pid"

function Write-Log {
    param([string]$Level, [string]$Message)
    $timestamp = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
    $logLine = "[$timestamp] [$Level] $Message"
    if (Test-Path $LogsDir) {
        Add-Content -Path $StartupLog -Value $logLine -Encoding UTF8
    }
    Write-Output $logLine
}

Write-Log "INFO" "================ PENGHENTIAN CHATBOT SERVICE ================"

$stoppedAny = $false

# 1. Cek dari file PID
if (Test-Path $PidFile) {
    $targetPidStr = (Get-Content $PidFile -Raw -ErrorAction SilentlyContinue).Trim()
    if ($targetPidStr -match '^\d+$') {
        $targetPid = [int]$targetPidStr
        $p = Get-Process -Id $targetPid -ErrorAction SilentlyContinue
        if ($p -and ($p.ProcessName -like "*node*")) {
            Stop-Process -Id $targetPid -Force -ErrorAction SilentlyContinue
            Write-Log "INFO" "Proses chatbot (PID: $targetPid) berhasil dihentikan."
            $stoppedAny = $true
        } else {
            Write-Log "INFO" "PID $targetPid dari file PID tidak lagi berjalan."
        }
    }
    Remove-Item $PidFile -Force -ErrorAction SilentlyContinue
}

# 2. Cek koneksi port 3001 (khusus proses Node yang membuka port 3001)
$conn = Get-NetTCPConnection -LocalPort 3001 -State Listen -ErrorAction SilentlyContinue
if ($conn) {
    $listenPid = $conn.OwningProcess
    $p = Get-Process -Id $listenPid -ErrorAction SilentlyContinue
    if ($p -and ($p.ProcessName -like "*node*")) {
        Stop-Process -Id $listenPid -Force -ErrorAction SilentlyContinue
        Write-Log "INFO" "Proses Node.js pada port 3001 (PID: $listenPid) berhasil dihentikan."
        $stoppedAny = $true
    }
}

if ($stoppedAny) {
    Write-Log "SUCCESS" "Layanan Chatbot AI STMIK IKMI berhasil dinonaktifkan."
} else {
    Write-Log "INFO" "Layanan Chatbot AI sudah dalam kondisi tidak aktif."
}
