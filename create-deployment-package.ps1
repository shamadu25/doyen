# Doyen Auto Services - Production Deployment Package Creator
# This script creates a production-ready ZIP file for deployment

Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host " DOYEN AUTO SERVICES - Production Package Creator" -ForegroundColor Cyan
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Domain: https://doyen.cliqpos.com" -ForegroundColor Yellow
Write-Host ""

# Step 1: Clear caches
Write-Host "Step 1: Clearing caches..." -ForegroundColor Green
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
Write-Host "✓ Caches cleared" -ForegroundColor Green
Write-Host ""

# Step 2: Optimize for production
Write-Host "Step 2: Optimizing for production..." -ForegroundColor Green
php artisan config:cache
php artisan route:cache
php artisan view:cache
Write-Host "✓ Application optimized" -ForegroundColor Green
Write-Host ""

# Step 3: Compile assets
Write-Host "Step 3: Compiling production assets..." -ForegroundColor Green
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠ Warning: Asset compilation had issues. Continuing..." -ForegroundColor Yellow
}
Write-Host "✓ Assets compiled" -ForegroundColor Green
Write-Host ""

# Step 4: Create deployment directory
Write-Host "Step 4: Preparing deployment package..." -ForegroundColor Green
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$deployDir = "doyen-auto-production-$timestamp"
$zipFile = "$deployDir.zip"

# Remove old deployment packages
if (Test-Path "doyen-auto-production-*.zip") {
    Write-Host "  Removing old deployment packages..." -ForegroundColor Gray
    Remove-Item "doyen-auto-production-*.zip" -Force
}

# Create temporary directory
if (Test-Path $deployDir) {
    Remove-Item $deployDir -Recurse -Force
}
New-Item -ItemType Directory -Path $deployDir | Out-Null

Write-Host "  Created: $deployDir" -ForegroundColor Gray
Write-Host ""

# Step 5: Copy files (excluding unnecessary ones)
Write-Host "Step 5: Copying application files..." -ForegroundColor Green

# Folders to include
$foldersToInclude = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage",
    "vendor"
)

# Files to include in root
$filesToInclude = @(
    "artisan",
    "composer.json",
    "composer.lock",
    "package.json"
)

foreach ($folder in $foldersToInclude) {
    if (Test-Path $folder) {
        Write-Host "  Copying $folder/..." -ForegroundColor Gray
        Copy-Item -Path $folder -Destination "$deployDir\$folder" -Recurse -Force
    }
}

foreach ($file in $filesToInclude) {
    if (Test-Path $file) {
        Write-Host "  Copying $file" -ForegroundColor Gray
        Copy-Item -Path $file -Destination "$deployDir\$file" -Force
    }
}

# Copy .env.production
if (Test-Path ".env.production") {
    Write-Host "  Copying .env.production" -ForegroundColor Gray
    Copy-Item -Path ".env.production" -Destination "$deployDir\.env.production" -Force
}

# Copy .htaccess from public if exists
if (Test-Path "public\.htaccess") {
    Write-Host "  Copying .htaccess" -ForegroundColor Gray
    Copy-Item -Path "public\.htaccess" -Destination "$deployDir\public\.htaccess" -Force
}

Write-Host "✓ Files copied" -ForegroundColor Green
Write-Host ""

# Step 6: Clean up unnecessary files from deployment package
Write-Host "Step 6: Cleaning up unnecessary files..." -ForegroundColor Green

# Remove test files
$testFiles = @(
    "comprehensive-test.php",
    "phase1-test.php",
    "phase2-test.php",
    "test-*.php",
    "deployment-ready-check.php",
    "check-vehicles-columns.php",
    "functional-test.php",
    "system-test.php",
    "api-status-report.php"
)

foreach ($file in $testFiles) {
    $path = "$deployDir\$file"
    if (Test-Path $path) {
        Remove-Item $path -Force
        Write-Host "  Removed: $file" -ForegroundColor Gray
    }
}

# Remove .bat files
if (Test-Path "$deployDir\*.bat") {
    Remove-Item "$deployDir\*.bat" -Force
    Write-Host "  Removed: *.bat files" -ForegroundColor Gray
}

# Clean storage directories
Write-Host "  Cleaning storage cache..." -ForegroundColor Gray
if (Test-Path "$deployDir\storage\framework\cache\data\*") {
    Remove-Item "$deployDir\storage\framework\cache\data\*" -Recurse -Force -ErrorAction SilentlyContinue
}
if (Test-Path "$deployDir\storage\framework\sessions\*") {
    Remove-Item "$deployDir\storage\framework\sessions\*" -Force -ErrorAction SilentlyContinue
}
if (Test-Path "$deployDir\storage\framework\views\*") {
    Remove-Item "$deployDir\storage\framework\views\*" -Force -ErrorAction SilentlyContinue
}
if (Test-Path "$deployDir\storage\logs\*.log") {
    Remove-Item "$deployDir\storage\logs\*.log" -Force -ErrorAction SilentlyContinue
}

# Clean bootstrap cache
if (Test-Path "$deployDir\bootstrap\cache\*") {
    Get-ChildItem "$deployDir\bootstrap\cache" -Exclude ".gitignore" | Remove-Item -Force -ErrorAction SilentlyContinue
}

Write-Host "✓ Cleanup complete" -ForegroundColor Green
Write-Host ""

# Step 7: Create README for deployment
Write-Host "Step 7: Creating deployment instructions..." -ForegroundColor Green

$readmeContent = "========================================================`n"
$readmeContent += " DOYEN AUTO SERVICES - Production Deployment Package`n"
$readmeContent += "========================================================`n`n"
$readmeContent += "Domain: https://doyen.cliqpos.com`n"
$readmeContent += "Created: $timestamp`n`n"
$readmeContent += "1. Upload ZIP to server and extract`n"
$readmeContent += "2. Rename .env.production to .env`n"
$readmeContent += "3. Update database credentials in .env`n"
$readmeContent += "4. Set permissions: chmod -R 775 storage bootstrap/cache`n"
$readmeContent += "5. Run: php artisan migrate --force`n"
$readmeContent += "6. Run: php artisan db:seed --class=RolesAndPermissionsSeeder --force`n"
$readmeContent += "7. Setup cron job (see LIVE_SERVER_DEPLOYMENT.md)`n"
$readmeContent += "8. Install SSL certificate`n"
$readmeContent += "9. Test at https://doyen.cliqpos.com`n`n"
$readmeContent += "Full guide: See LIVE_SERVER_DEPLOYMENT.md`n"

Set-Content -Path "$deployDir\DEPLOYMENT_README.txt" -Value $readmeContent
Write-Host "✓ Deployment instructions created" -ForegroundColor Green
Write-Host ""

# Step 8: Create ZIP file
Write-Host "Step 8: Creating ZIP archive..." -ForegroundColor Green
Write-Host "  This may take a few minutes..." -ForegroundColor Gray

# Compress with maximum compression
Compress-Archive -Path "$deployDir\*" -DestinationPath $zipFile -CompressionLevel Optimal -Force

# Get file size
$zipSize = (Get-Item $zipFile).Length / 1MB
$zipSizeFormatted = "{0:N2} MB" -f $zipSize

Write-Host "✓ ZIP file created: $zipFile" -ForegroundColor Green
Write-Host "  Size: $zipSizeFormatted" -ForegroundColor Gray
Write-Host ""

# Step 9: Cleanup temporary directory
Write-Host "Step 9: Cleaning up..." -ForegroundColor Green
Remove-Item $deployDir -Recurse -Force
Write-Host "✓ Temporary files removed" -ForegroundColor Green
Write-Host ""

# Final summary
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host " PRODUCTION PACKAGE READY!" -ForegroundColor Cyan
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Package File: $zipFile" -ForegroundColor Yellow -BackgroundColor Black
Write-Host "Package Size: $zipSizeFormatted" -ForegroundColor Yellow
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Green
Write-Host "1. Upload $zipFile to your server" -ForegroundColor White
Write-Host "2. Extract the ZIP file" -ForegroundColor White
Write-Host "3. Follow instructions in DEPLOYMENT_README.txt" -ForegroundColor White
Write-Host "4. Point domain to 'public/' folder" -ForegroundColor White
Write-Host "5. Configure database in .env" -ForegroundColor White
Write-Host "6. Run setup commands (see README)" -ForegroundColor White
Write-Host "7. Setup cron job for scheduler" -ForegroundColor White
Write-Host "8. Install SSL certificate" -ForegroundColor White
Write-Host "9. Test at https://doyen.cliqpos.com" -ForegroundColor White
Write-Host ""
Write-Host "Full guide: LIVE_SERVER_DEPLOYMENT.md" -ForegroundColor Cyan
Write-Host ""
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host ""
