# Doyen Auto Services - Windows Task Scheduler Setup
# Run this script as Administrator once to register scheduled tasks

$phpPath    = "C:\xampp\php\php.exe"
$artisan    = "C:\xampp\htdocs\garage\garage\artisan"
$projectDir = "C:\xampp\htdocs\garage\garage"

Write-Host "Setting up Doyen Auto Services scheduled tasks..." -ForegroundColor Cyan

# -- 1. Laravel Scheduler (every minute) -----------------------------------
$schedulerAction  = New-ScheduledTaskAction -Execute $phpPath -Argument "$artisan schedule:run" -WorkingDirectory $projectDir
$schedulerTrigger = New-ScheduledTaskTrigger -RepetitionInterval (New-TimeSpan -Minutes 1) -Once -At (Get-Date)
$schedulerSettings = New-ScheduledTaskSettingsSet -ExecutionTimeLimit (New-TimeSpan -Minutes 5) -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 1)

Register-ScheduledTask `
    -TaskName    "DoyenAuto - Laravel Scheduler" `
    -Action      $schedulerAction `
    -Trigger     $schedulerTrigger `
    -Settings    $schedulerSettings `
    -RunLevel    Highest `
    -Force | Out-Null

Write-Host "[OK] Laravel Scheduler registered (runs every minute)" -ForegroundColor Green

# -- 2. Queue Worker --------------------------------------------------------
$queueAction   = New-ScheduledTaskAction -Execute $phpPath -Argument "$artisan queue:work --sleep=3 --tries=3 --max-time=3600" -WorkingDirectory $projectDir
$queueTrigger  = New-ScheduledTaskTrigger -AtStartup
$queueSettings = New-ScheduledTaskSettingsSet -ExecutionTimeLimit (New-TimeSpan -Hours 0) -RestartCount 5 -RestartInterval (New-TimeSpan -Minutes 2)

Register-ScheduledTask `
    -TaskName    "DoyenAuto - Laravel Queue Worker" `
    -Action      $queueAction `
    -Trigger     $queueTrigger `
    -Settings    $queueSettings `
    -RunLevel    Highest `
    -Force | Out-Null

Write-Host "[OK] Queue Worker registered (starts at system startup)" -ForegroundColor Green

Write-Host ""
Write-Host "All tasks registered. Starting queue worker now..." -ForegroundColor Cyan
Start-ScheduledTask -TaskName "DoyenAuto - Laravel Queue Worker"
Write-Host "[OK] Queue worker started" -ForegroundColor Green

Write-Host ""
Write-Host "Setup complete! Reminders will now send automatically:" -ForegroundColor Green
Write-Host "  - Appointment reminders: 24h and 1h before" -ForegroundColor White
Write-Host "  - MOT reminders: 30, 14, 7 and 3 days before expiry" -ForegroundColor White
Write-Host "  - Service reminders: 30 days before due" -ForegroundColor White
Write-Host "  - Review requests: daily at 11:00" -ForegroundColor White
