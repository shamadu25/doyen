@echo off
echo ============================================================
echo  DOYEN AUTO SERVICES - Production Deployment Preparation
echo ============================================================
echo.
echo This will prepare your files for live server deployment
echo Domain: https://doyen.cliqpos.com
echo.
pause
echo.
echo Step 1: Clearing caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
call php artisan view:clear
echo ✓ Caches cleared
echo.

echo Step 2: Optimizing for production...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
echo ✓ Application optimized
echo.

echo Step 3: Compiling assets...
call npm run build
echo ✓ Assets compiled
echo.

echo ============================================================
echo  DEPLOYMENT PREPARATION COMPLETE!
echo ============================================================
echo.
echo NEXT STEPS:
echo.
echo 1. Upload files to your server (see LIVE_SERVER_DEPLOYMENT.md)
echo 2. Upload .env.production and rename to .env
echo 3. Update database credentials in .env on server
echo 4. Run migrations: php artisan migrate --force
echo 5. Seed roles: php artisan db:seed --class=RolesAndPermissionsSeeder
echo 6. Set permissions: chmod -R 775 storage bootstrap/cache
echo 7. Create storage link: php artisan storage:link
echo 8. Setup cron job for scheduler
echo 9. Install SSL certificate
echo 10. Test the site!
echo.
echo See LIVE_SERVER_DEPLOYMENT.md for detailed instructions.
echo.
pause
