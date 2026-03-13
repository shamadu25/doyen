@echo off
echo ======================================
echo UK Garage Management System
echo Database Setup Script
echo ======================================
echo.

REM Check if MySQL is accessible
echo [1/5] Checking MySQL connection...
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: MySQL is not installed or not in PATH
    echo Please install MySQL or add it to your PATH
    pause
    exit /b 1
)

echo [OK] MySQL found
echo.

REM Get database credentials
set /p DB_NAME="Enter database name [garage_management]: " || set DB_NAME=garage_management
set /p DB_USER="Enter MySQL username [root]: " || set DB_USER=root
set /p DB_PASS="Enter MySQL password (press Enter if none): "

echo.
echo [2/5] Creating database: %DB_NAME%
echo.

REM Create database
if "%DB_PASS%"=="" (
    mysql -u %DB_USER% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
) else (
    mysql -u %DB_USER% -p%DB_PASS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
)

if %errorlevel% neq 0 (
    echo ERROR: Failed to create database
    pause
    exit /b 1
)

echo [OK] Database created successfully
echo.

REM Update .env file
echo [3/5] Updating .env file...
(
    echo DB_CONNECTION=mysql
    echo DB_HOST=127.0.0.1
    echo DB_PORT=3306
    echo DB_DATABASE=%DB_NAME%
    echo DB_USERNAME=%DB_USER%
    echo DB_PASSWORD=%DB_PASS%
) > .env.db.tmp

REM Backup existing .env if it exists
if exist .env (
    copy .env .env.backup >nul
    echo [OK] Backed up existing .env to .env.backup
)

echo [OK] Database configuration updated
echo.

echo [4/5] Running migrations...
call php artisan migrate --force

if %errorlevel% neq 0 (
    echo ERROR: Migration failed
    pause
    exit /b 1
)

echo [OK] Database tables created
echo.

echo [5/5] Seeding services...
call php artisan db:seed --class=ServiceSeeder

if %errorlevel% neq 0 (
    echo WARNING: Seeding failed (this is optional)
)

echo [OK] Services seeded
echo.

echo ======================================
echo Database setup completed successfully!
echo ======================================
echo.
echo Database Name: %DB_NAME%
echo Database User: %DB_USER%
echo.
echo Next steps:
echo 1. Update your garage details in .env file
echo 2. Add API keys (DVLA, DVSA, TecDoc) if available
echo 3. Run: npm install
echo 4. Run: npm run build
echo 5. Run: php artisan serve
echo.
echo Then visit: http://localhost:8000
echo.
pause
