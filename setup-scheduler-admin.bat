@echo off
echo Registering Doyen Auto Services scheduled tasks...
echo (This window must be run as Administrator)
echo.

setlocal

set PHP=C:\xampp\php\php.exe
set ARTISAN=C:\xampp\htdocs\garage\garage\artisan
set WORKDIR=C:\xampp\htdocs\garage\garage

REM -- Laravel Scheduler (every minute) --
schtasks /create /tn "DoyenAuto - Laravel Scheduler" ^
    /tr "\"%PHP%\" \"%ARTISAN%\" schedule:run" ^
    /sc MINUTE /mo 1 ^
    /rl HIGHEST /f
echo [OK] Laravel Scheduler task registered

REM -- Queue Worker (at startup) --
schtasks /create /tn "DoyenAuto - Queue Worker" ^
    /tr "\"%PHP%\" \"%ARTISAN%\" queue:work --sleep=3 --tries=3 --max-time=3600" ^
    /sc ONSTART ^
    /rl HIGHEST /f
echo [OK] Queue Worker task registered

REM -- Start queue worker now --
schtasks /run /tn "DoyenAuto - Queue Worker"
echo [OK] Queue worker started

echo.
echo Done! Automated reminders are now active:
echo   - Appointment reminders: 24h and 1h before each booking
echo   - MOT reminders: 30 / 14 / 7 / 3 days before expiry
echo   - Service reminders: 30 days before due
echo   - Review requests: daily at 11:00
echo.
pause
