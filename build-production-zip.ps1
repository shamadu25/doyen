# Production ZIP builder for doyen.quikerp.com
# Run from: c:\xampp\htdocs\garage\garage\
# Output: garage-production.zip (on Desktop)

$ProjectRoot = "c:\xampp\htdocs\garage\garage"
$OutputZip   = "$env:USERPROFILE\Desktop\garage-production.zip"
$TempDir     = "$env:TEMP\garage-build-$(Get-Random)"

Write-Host "=== Garage Production ZIP Builder ===" -ForegroundColor Cyan
Write-Host "Source : $ProjectRoot"
Write-Host "Output : $OutputZip"

# --- 1. Install production-only vendor ---
Write-Host "`n[1/4] Installing production Composer dependencies..." -ForegroundColor Yellow
Set-Location $ProjectRoot
composer install --no-dev --optimize-autoloader --quiet
if ($LASTEXITCODE -ne 0) { Write-Host "Composer failed!" -ForegroundColor Red; exit 1 }
Write-Host "      Done." -ForegroundColor Green

# --- 2. Build frontend assets ---
Write-Host "`n[2/4] Building frontend assets (npm run build)..." -ForegroundColor Yellow
npm run build --silent
if ($LASTEXITCODE -ne 0) { Write-Host "npm build failed!" -ForegroundColor Red; exit 1 }
Write-Host "      Done." -ForegroundColor Green

# --- 3. Copy files to temp staging directory ---
Write-Host "`n[3/4] Staging files..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $TempDir -Force | Out-Null

# Directories to include
$includeDirs = @(
    "app", "bootstrap", "config", "database",
    "public", "resources", "routes", "storage", "vendor"
)

foreach ($dir in $includeDirs) {
    $src = Join-Path $ProjectRoot $dir
    $dst = Join-Path $TempDir $dir
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $dst -Recurse -Force
        Write-Host "      Copied: $dir" -ForegroundColor DarkGray
    }
}

# Individual files to include
$includeFiles = @(
    "artisan", "composer.json", "composer.lock"
)

foreach ($file in $includeFiles) {
    $src = Join-Path $ProjectRoot $file
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $TempDir -Force
        Write-Host "      Copied: $file" -ForegroundColor DarkGray
    }
}

# Create a production .env template (NOT the real .env — server keeps its own)
$envTemplate = @"
APP_NAME="Garage Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://doyen.quikerp.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doyen.quikerp.com
MAIL_FROM_NAME="Garage Management"
"@
$envTemplate | Out-File -FilePath "$TempDir\.env.production-template" -Encoding UTF8

# Remove log files and cache files from the staged copy
Remove-Item "$TempDir\storage\logs\*.log" -Force -ErrorAction SilentlyContinue
Remove-Item "$TempDir\bootstrap\cache\*.php" -Force -ErrorAction SilentlyContinue
# Remove node_modules if accidentally copied into public
Remove-Item "$TempDir\public\node_modules" -Recurse -Force -ErrorAction SilentlyContinue
# Ensure storage directories exist (with .gitkeep)
$storageDirs = @(
    "storage\app\public",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs"
)
foreach ($d in $storageDirs) {
    $path = Join-Path $TempDir $d
    New-Item -ItemType Directory -Path $path -Force | Out-Null
}

Write-Host "      Staging complete." -ForegroundColor Green

# --- 4. Create ZIP ---
Write-Host "`n[4/4] Creating ZIP archive..." -ForegroundColor Yellow
if (Test-Path $OutputZip) { Remove-Item $OutputZip -Force }
Compress-Archive -Path "$TempDir\*" -DestinationPath $OutputZip -CompressionLevel Optimal
Remove-Item $TempDir -Recurse -Force

$zipSize = [math]::Round((Get-Item $OutputZip).Length / 1MB, 1)
Write-Host "      Done! ZIP size: ${zipSize} MB" -ForegroundColor Green

Write-Host "`n=== BUILD COMPLETE ===" -ForegroundColor Cyan
Write-Host "ZIP file: $OutputZip" -ForegroundColor White
Write-Host ""
Write-Host "AFTER UPLOADING to doyen.quikerp.com:" -ForegroundColor Yellow
Write-Host "  1. Extract zip to ~/www/doyen.quikerp.com/garage/"
Write-Host "  2. Create .env from .env.production-template (fill in DB details)"
Write-Host "  3. Run: php artisan key:generate"
Write-Host "  4. Run: php artisan migrate --force"
Write-Host "  5. Run: php artisan config:cache"
Write-Host "  6. Run: php artisan route:cache"
Write-Host "  7. Run: php artisan storage:link"
Write-Host "  8. Set document root to: ~/www/doyen.quikerp.com/garage/public"
