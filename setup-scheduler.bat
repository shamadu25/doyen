@echo off
echo ============================================================
echo  Doyen Auto Services - Task Scheduler Setup Helper
echo ============================================================
echo.
echo This will help you create the Windows scheduled task.
echo.
echo COPY THE VALUES BELOW FOR TASK SCHEDULER:
echo ============================================================
echo.
echo Task Name:
echo   Laravel Scheduler - Doyen Auto
echo.
echo Description:
echo   Runs automated appointment and MOT reminders every minute
echo.
echo Trigger:
echo   Daily, starting today at 12:00 AM
echo   Recur every 1 days
echo   ADVANCED: Repeat every 1 minute, indefinitely
echo.
echo Action - Program/Script:
echo   C:\xampp\php\php.exe
echo.
echo Action - Arguments:
echo   artisan schedule:run
echo.
echo Action - Start in:
echo   C:\xampp\htdocs\garage\garage
echo.
echo ============================================================
echo.
echo MANUAL STEPS:
echo 1. Press Win + R
echo 2. Type: taskschd.msc
echo 3. Press Enter
echo 4. Click "Create Basic Task"
echo 5. Use the values shown above
echo 6. IMPORTANT: Edit trigger, check "Repeat every 1 minute"
echo.
echo ============================================================
echo.
echo After setup, verify with:
echo   php artisan schedule:list
echo.
pause
