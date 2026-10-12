param(
    [int[]]$Ports = @(8088, 8081, 19999, 5050, 8083, 9000, 9001, 15432, 15433, 13306, 13307, 6379, 6380, 50010),
    [string]$ListenAddress = "0.0.0.0"
)

$ErrorActionPreference = "Stop"

function Test-IsAdministrator {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = [Security.Principal.WindowsPrincipal]::new($identity)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

function Get-WslPrimaryIp {
    $output = & wsl -e sh -lc "hostname -I" 2>$null
    if (-not $output) {
        throw "Tidak bisa membaca IP WSL."
    }

    $tokens = ($output -join " ") -split "\s+" | Where-Object { $_ -match '^\d{1,3}(\.\d{1,3}){3}$' }
    if (-not $tokens) {
        throw "IP WSL tidak ditemukan dari output hostname -I."
    }

    foreach ($candidate in $tokens) {
        if ($candidate -like "172.*" -or $candidate -like "10.*" -or $candidate -like "192.168.*") {
            return $candidate
        }
    }

    return $tokens[0]
}

if (-not (Test-IsAdministrator)) {
    throw "Sinkronisasi portproxy butuh PowerShell Run as Administrator."
}

$wslIp = Get-WslPrimaryIp
Write-Host "WSL IP aktif: $wslIp"

foreach ($port in $Ports) {
    & netsh interface portproxy delete v4tov4 listenaddress=$ListenAddress listenport=$port | Out-Null
    & netsh interface portproxy add v4tov4 listenaddress=$ListenAddress listenport=$port connectaddress=$wslIp connectport=$port | Out-Null

    $ruleName = "Klinik Docker TCP $port"
    if (-not (Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue)) {
        New-NetFirewallRule -DisplayName $ruleName -Direction Inbound -Action Allow -Protocol TCP -LocalPort $port | Out-Null
    }
}

Write-Host ("Portproxy aktif untuk port: " + ($Ports -join ", "))
