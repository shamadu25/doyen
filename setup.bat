@echo off
echo ================================
echo Garage Management System Setup
echo ================================
echo.

echo Step 1: Installing Composer dependencies...
call composer install
if errorlevel 1 (
    echo ERROR: Composer install failed!
    pause
    exit /b 1
)
echo ✓ Composer dependencies installed
echo.

echo Step 2: Installing NPM packages...
call npm install
if errorlevel 1 (
    echo ERROR: NPM install failed!
    pause
    exit /b 1
)
echo ✓ NPM packages installed
echo.

echo Step 3: Copying environment file...
if not exist .env (
    copy .env.example .env
    echo ✓ .env file created
) else (
    echo .env file already exists, skipping...
)
echo.

echo Step 4: Generating application key...
call php artisan key:generate
echo ✓ Application key generated
echo.

echo Step 5: Building frontend assets...
call npm run build
if errorlevel 1 (
    echo ERROR: Asset build failed!
    pause
    exit /b 1
)
echo ✓ Frontend assets built
echo.

echo ================================
echo Setup Complete!
echo ================================
echo.
echo IMPORTANT: Before running the application:
echo 1. Update your .env file with database credentials
echo 2. Add your DVLA, DVSA, and TecDoc API keys
echo 3. Run: php artisan migrate
echo 4. Run: php artisan db:seed --class=ServiceSeeder
echo 5. Run: php artisan serve
echo.
echo Then visit: http://localhost:8000
echo.
pause
