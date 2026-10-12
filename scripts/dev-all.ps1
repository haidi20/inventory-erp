param(
    [switch]$PrintOnly
)

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$beScript = Join-Path $projectRoot "scripts\dev-be.ps1"
$feScript = Join-Path $projectRoot "scripts\dev-fe.ps1"

if ($PrintOnly) {
    Write-Output "powershell -NoProfile -ExecutionPolicy Bypass -File `"$beScript`""
    Write-Output "powershell -NoProfile -ExecutionPolicy Bypass -File `"$feScript`""
    exit 0
}

Write-Host "Membuka backend dan frontend dalam jendela terpisah..." -ForegroundColor Cyan
Start-Process powershell -ArgumentList @("-NoExit", "-ExecutionPolicy", "Bypass", "-File", $beScript)
Start-Process powershell -ArgumentList @("-NoExit", "-ExecutionPolicy", "Bypass", "-File", $feScript)
