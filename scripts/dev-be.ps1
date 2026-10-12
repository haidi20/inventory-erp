param(
    [switch]$PrintOnly
)

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$bePath = Join-Path $projectRoot "be"
$commandText = "cd /d `"$bePath`" && go run ."

if ($PrintOnly) {
    Write-Output $commandText
    exit 0
}

Set-Location $bePath
Write-Host "Menjalankan backend Go dari $bePath" -ForegroundColor Cyan
go run .
