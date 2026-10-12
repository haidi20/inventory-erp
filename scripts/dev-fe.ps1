param(
    [switch]$PrintOnly
)

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$fePath = Join-Path $projectRoot "fe"
$commandText = "cd /d `"$fePath`" && npm run dev"

if ($PrintOnly) {
    Write-Output $commandText
    exit 0
}

Set-Location $fePath
Write-Host "Menjalankan frontend Vue dari $fePath" -ForegroundColor Cyan
& "E:\apps\nodejs\npm.cmd" run dev
