# ==============================================================================
# Script Startup Layanan Chatbot AI - Sistem Informasi Fasilitas STMIK IKMI
# Menjalankan server Node.js di background dengan penanganan duplikasi & koneksi MySQL
# ==============================================================================

$ErrorActionPreference = "Continue"

# Direktori kerja berbasis path skrip saat ini (aman terhadap path berspasi)
$ServiceDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$LogsDir    = Join-Path $ServiceDir "logs"
$ServerJs   = Join-Path $ServiceDir "server.js"

if (-not (Test-Path $LogsDir)) {
    New-Item -ItemType Directory -Path $LogsDir -Force | Out-Null
}

$StartupLog    = Join-Path $LogsDir "startup.log"
$ChatbotLog    = Join-Path $LogsDir "chatbot.log"
$ChatbotErrLog = Join-Path $LogsDir "chatbot.err.log"
$PidFile       = Join-Path $LogsDir "chatbot.pid"

function Write-Log {
    param([string]$Level, [string]$Message)
    $timestamp = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
    $logLine = "[$timestamp] [$Level] $Message"
    Add-Content -Path $StartupLog -Value $logLine -Encoding UTF8
    Write-Output $logLine
}

function Test-HttpEndpoint {
    param([string]$Url = "http://127.0.0.1:3001/api/health", [int]$TimeoutSec = 2)
    try {
        $request = [System.Net.WebRequest]::Create($Url)
        $request.Timeout = $TimeoutSec * 1000
        $request.Method = "GET"
        $response = $request.GetResponse()
        $httpCode = [int]$response.StatusCode
        $response.Close()
        return ($httpCode -eq 200)
    } catch {
        return $false
    }
}

function Test-TcpPort {
    param([string]$HostName = "127.0.0.1", [int]$Port = 3306, [int]$TimeoutMs = 1000)
    try {
        $tcpClient = New-Object System.Net.Sockets.TcpClient
        $asyncResult = $tcpClient.BeginConnect($HostName, $Port, $null, $null)
        $waitSuccess = $asyncResult.AsyncWaitHandle.WaitOne($TimeoutMs, $false)
        if ($waitSuccess -and $tcpClient.Connected) {
            $tcpClient.EndConnect($asyncResult)
            $tcpClient.Close()
            return $true
        }
        $tcpClient.Close()
        return $false
    } catch {
        return $false
    }
}

Write-Log "INFO" "================ INSIALISASI STARTUP CHATBOT SERVICE ================"

# ------------------------------------------------------------------------------
# 1. PENCEGAHAN PROSES GANDA (SINGLE INSTANCE CHECK)
# ------------------------------------------------------------------------------
if (Test-HttpEndpoint -Url "http://127.0.0.1:3001/api/health" -TimeoutSec 2) {
    $existingPid = "unknown"
    if (Test-Path $PidFile) {
        $existingPid = (Get-Content $PidFile -Raw -ErrorAction SilentlyContinue).Trim()
    }
    Write-Log "INFO" "Chatbot service sudah berjalan dan merespons pada port 3001 (PID terdata: $existingPid). Melewati inisialisasi untuk mencegah proses duplikat."
    exit 0
}

# Bersihkan file PID kedaluwarsa jika ada proses lama yang mati
if (Test-Path $PidFile) {
    $oldPid = (Get-Content $PidFile -Raw -ErrorAction SilentlyContinue).Trim()
    if ($oldPid -match '^\d+$') {
        $oldProc = Get-Process -Id ([int]$oldPid) -ErrorAction SilentlyContinue
        if (-not $oldProc) {
            Remove-Item $PidFile -Force -ErrorAction SilentlyContinue
        }
    }
}

# ------------------------------------------------------------------------------
# 2. VALIDASI KETERSEDIAAN NODE.JS
# ------------------------------------------------------------------------------
$nodeCmd = Get-Command node -ErrorAction SilentlyContinue
$nodePath = if ($nodeCmd) { $nodeCmd.Source } else { "C:\Program Files\nodejs\node.exe" }

if (-not (Test-Path $nodePath)) {
    Write-Log "ERROR" "Node.js executable tidak ditemukan di '$nodePath'. Pastikan Node.js terinstal pada sistem."
    exit 1
}

# ------------------------------------------------------------------------------
# 3. KESIAPAN LAYANAN LARAGON / MYSQL (PORT 3306)
# ------------------------------------------------------------------------------
$maxWaitSec = 25
$waited = 0
$mysqlReady = $false

Write-Log "INFO" "Memeriksa kesiapan basis data MySQL di 127.0.0.1:3306 (timeout: ${maxWaitSec}s)..."

while ($waited -lt $maxWaitSec) {
    if (Test-TcpPort -HostName "127.0.0.1" -Port 3306 -TimeoutMs 800) {
        $mysqlReady = $true
        break
    }
    Start-Sleep -Seconds 2
    $waited += 2
}

if ($mysqlReady) {
    Write-Log "INFO" "Database MySQL terdeteksi AKTIF pada port 3306 (waktu tunggu: ${waited} detik)."
} else {
    Write-Log "WARN" "Database MySQL belum aktif setelah ${maxWaitSec} detik (Laragon kemungkinan masih proses startup). Chatbot tetap diluncurkan dengan mode connection pool fallback."
}

# ------------------------------------------------------------------------------
# 4. MENJALANKAN SERVER NODE.JS SECARA BACKGROUND (DETACHED WMI PROCESS)
# ------------------------------------------------------------------------------
Write-Log "INFO" "Meluncurkan daemon Node.js via WMI Process dari folder: '$ServiceDir'..."

$cmdLine = "cmd.exe /c `"`"$nodePath`" server.js >> `"$ChatbotLog`" 2>> `"$ChatbotErrLog`"`""

$processClass = [wmiclass]"Win32_Process"
$startup = [wmiclass]"Win32_ProcessStartup"
$startupInstance = $startup.CreateInstance()
$startupInstance.ShowWindow = 0 # Hidden (tanpa jendela konsol)

$res = $processClass.Create($cmdLine, $ServiceDir, $startupInstance)

if ($res.ReturnValue -ne 0) {
    Write-Log "ERROR" "Gagal membuat proses WMI. ReturnValue: $($res.ReturnValue)"
    exit 1
}

Write-Log "INFO" "Proses launcher berhasil dibuat dengan PID: $($res.ProcessId)."

# ------------------------------------------------------------------------------
# 5. VERIFIKASI POST-STARTUP (HEALTHCHECK /api/health) & SIMPAN PID AKTIF
# ------------------------------------------------------------------------------
$ready = $false
for ($i = 1; $i -le 10; $i++) {
    Start-Sleep -Seconds 1
    if (Test-HttpEndpoint -Url "http://127.0.0.1:3001/api/health" -TimeoutSec 1) {
        $ready = $true
        break
    }
}

$activePid = "unknown"
$conn = Get-NetTCPConnection -LocalPort 3001 -State Listen -ErrorAction SilentlyContinue
if ($conn -and $conn.OwningProcess) {
    $activePid = [string]$conn.OwningProcess
    Set-Content -Path $PidFile -Value $activePid -Encoding ASCII
} elseif ($res.ProcessId) {
    $activePid = [string]$res.ProcessId
    Set-Content -Path $PidFile -Value $activePid -Encoding ASCII
}

if ($ready) {
    Write-Log "SUCCESS" "Layanan Chatbot AI aktif dan berhasil merespons pada http://127.0.0.1:3001/api/health (PID Node: $activePid)."
    exit 0
} else {
    Write-Log "WARN" "Daemon diluncurkan (PID: $activePid), namun endpoint healthcheck belum merespons dalam 10 detik. Silakan periksa '$ChatbotErrLog'."
    exit 0
}


