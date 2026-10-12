# Jalankan script ini dari mana saja, ia akan mencari root src/
$scriptPath = $MyInvocation.MyCommand.Path
$accountingFolder = Split-Path -Parent $scriptPath
$root = (Get-Item $accountingFolder).Parent.Parent.Parent.FullName

Write-Output "Project Root: $root"

Set-Location $root

# Cari semua file test di folder Accounting
$testFiles = Get-ChildItem -Path "tests/Feature/Accounting" -Filter "*Test.php" -Recurse | Where-Object { $_.Name -ne "ExampleTest.php" -and $_.Name -ne "run_tests.ps1" }

$results = @()
foreach ($file in $testFiles) {
    # Ambil path relatif terhadap root (format forward slash untuk PHP)
    $relativePath = $file.FullName.Replace($root + "\", "").Replace("\", "/")
    
    Write-Output "`n[RUNNING] $relativePath" -NoNewline
    
    $output = php artisan test $relativePath --display-errors 2>&1 | Out-String
    
    if ($output -match "PASS" -and -not ($output -match "FAIL")) {
        Write-Output " -> PASSED"
        $results += [PSCustomObject]@{ File = $relativePath; Status = "PASSED" }
    } elseif ($output -match "skipped") {
        Write-Output " -> SKIPPED"
        $results += [PSCustomObject]@{ File = $relativePath; Status = "SKIPPED" }
    } else {
        Write-Output " -> FAILED"
        $results += [PSCustomObject]@{ File = $relativePath; Status = "FAILED"; Output = $output }
    }
}

Write-Output "`n`n================ TEST SUMMARY ================"
foreach ($res in $results) {
    Write-Output ("[{0}] {1}" -f $res.Status, $res.File)
}
