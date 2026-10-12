@echo off
set LOG_FILE=D:\projects\klinik-starter\src\tests\accounting_results.log
set SUMMARY_FILE=D:\projects\klinik-starter\src\tests\test_summary.txt
set PROJECT_ROOT=D:\projects\klinik-starter\src

echo [START] Accounting Report Audit - %DATE% %TIME% > %LOG_FILE%
echo Accounting Module Test Summary > %SUMMARY_FILE%
echo ------------------------------- >> %SUMMARY_FILE%

cd /d %PROJECT_ROOT%

:: 1. Statement of Changes in Equity
echo Running StatementOfChangesInEquityTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\StatementOfChangesInEquityTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] StatementOfChangesInEquityTest >> %SUMMARY_FILE%) else (echo [FAIL] StatementOfChangesInEquityTest >> %SUMMARY_FILE%)

:: 2. Adjusted Trial Balance
echo Running AdjustedTrialBalanceTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\AdjustedTrialBalanceTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] AdjustedTrialBalanceTest >> %SUMMARY_FILE%) else (echo [FAIL] AdjustedTrialBalanceTest >> %SUMMARY_FILE%)

:: 3. Post Closing Trial Balance
echo Running PostClosingTrialBalanceTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\PostClosingTrialBalanceTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] PostClosingTrialBalanceTest >> %SUMMARY_FILE%) else (echo [FAIL] PostClosingTrialBalanceTest >> %SUMMARY_FILE%)

:: 4. AR Aging
echo Running AccountsReceivableAgingTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\AccountsReceivableAgingTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] AccountsReceivableAgingTest >> %SUMMARY_FILE%) else (echo [FAIL] AccountsReceivableAgingTest >> %SUMMARY_FILE%)

:: 5. AP Aging
echo Running AccountsPayableAgingTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\AccountsPayableAgingTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] AccountsPayableAgingTest >> %SUMMARY_FILE%) else (echo [FAIL] AccountsPayableAgingTest >> %SUMMARY_FILE%)

:: 6. Inventory Report
echo Running InventoryReportTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\InventoryReportTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] InventoryReportTest >> %SUMMARY_FILE%) else (echo [FAIL] InventoryReportTest >> %SUMMARY_FILE%)

:: 7. Fixed Asset Report
echo Running FixedAssetReportTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\FixedAssetReportTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] FixedAssetReportTest >> %SUMMARY_FILE%) else (echo [FAIL] FixedAssetReportTest >> %SUMMARY_FILE%)

:: 8. Tax Report
echo Running TaxReportTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\TaxReportTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] TaxReportTest >> %SUMMARY_FILE%) else (echo [FAIL] TaxReportTest >> %SUMMARY_FILE%)

:: 9. Cross Validation
echo Running CrossValidationIntegrationTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\CrossValidationIntegrationTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] CrossValidationIntegrationTest >> %SUMMARY_FILE%) else (echo [FAIL] CrossValidationIntegrationTest >> %SUMMARY_FILE%)

:: 10. Access Control
echo Running ReportAccessControlTest...
php artisan test D:\projects\klinik-starter\src\tests\Feature\Accounting\ReportAccessControlTest.php >> %LOG_FILE% 2>&1
if %ERRORLEVEL% EQU 0 (echo [PASS] ReportAccessControlTest >> %SUMMARY_FILE%) else (echo [FAIL] ReportAccessControlTest >> %SUMMARY_FILE%)

echo ------------------------------- >> %SUMMARY_FILE%
echo [END] Audit Complete. Full log at %LOG_FILE% >> %SUMMARY_FILE%

:: Search for any failures in the log using grep logic (simplified for batch)
findstr /C:"FAIL" %LOG_FILE% > D:\projects\klinik-starter\src\tests\audit_errors.log

echo Done. Check %SUMMARY_FILE% for results.
