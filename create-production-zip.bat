@echo off
echo =========================================================
echo  DOYEN AUTO SERVICES - Production Package Creator
echo =========================================================
echo.
echo Domain: https://doyen.cliqpos.com
echo.

REM Get timestamp for unique filename
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set timestamp=%datetime:~0,8%_%datetime:~8,6%
set zipfile=doyen-auto-production-%timestamp%.zip

echo Creating production package: %zipfile%
echo.
echo This may take a few minutes...
echo.

REM Use PowerShell to create ZIP (it's built into Windows)
powershell -Command "$ProgressPreference = 'SilentlyContinue'; Write-Host 'Preparing files...'; $exclude = @('.git', 'node_modules', '.env', 'storage\logs\*.log', 'storage\framework\cache', 'storage\framework\sessions', 'storage\framework\views', 'bootstrap\cache\*', '*.bat', 'test*.php', '*-test.php', 'comprehensive-test.php', 'deployment-ready-check.php', 'check-vehicles-columns.php', 'phase1-test.php', 'phase2-test.php'); Write-Host 'Creating ZIP archive...'; $files = Get-ChildItem -Path . -Recurse | Where-Object { $keep = $true; foreach ($pattern in $exclude) { if ($_.FullName -like "*$pattern*") { $keep = $false; break; } }; $keep }; Compress-Archive -Path app, bootstrap, config, database, public, resources, routes, storage, vendor, artisan, composer.json, composer.lock, package.json, .env.production, .htaccess, LIVE_SERVER_DEPLOYMENT.md, DEPLOYMENT_QUICK_REFERENCE.md -DestinationPath '%zipfile%' -CompressionLevel Optimal -Force; $size = (Get-Item '%zipfile%').Length / 1MB; Write-Host ('ZIP created: %zipfile% ({0:N2} MB)' -f $size) -ForegroundColor Green"

if exist %zipfile% (
    echo.
    echo =========================================================
    echo  SUCCESS! Production package created
    echo =========================================================
    echo.
    echo File: %zipfile%
    echo.
    for %%A in (%zipfile%) do echo Size: %%~zA bytes
    echo.
    echo NEXT STEPS:
    echo 1. Upload this ZIP file to your server
    echo 2. Extract it on the server
    echo 3. Rename .env.production to .env
    echo 4. Update database credentials in .env
    echo 5. Follow instructions in LIVE_SERVER_DEPLOYMENT.md
    echo.
    echo =========================================================
) else (
    echo.
    echo ERROR: Failed to create ZIP file
    echo.
)

pause
