<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendServiceReminders extends Command
{
    protected $signature = 'service:send-reminders {--days=30}';
    protected $description = 'Send service due reminders to customers';

    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    public function handle()
    {
        $days = $this->option('days');
        
        // Find vehicles due for service based on mileage or time
        $vehicles = Vehicle::with(['customer', 'vehicleServices'])
            ->whereHas('customer', function($query) {
                $query->whereNotNull('phone');
            })
            ->get()
            ->filter(function($vehicle) use ($days) {
                return $vehicle->isServiceDue($days);
            });

        $sent = 0;
        $failed = 0;

        foreach ($vehicles as $vehicle) {
            if ($this->smsService->sendServiceReminder($vehicle, 'annual service')) {
                $sent++;
                $this->info("✓ Sent reminder for {$vehicle->registration}");
            } else {
                $failed++;
                $this->warn("✗ Failed for {$vehicle->registration}");
            }
        }

        $this->info("\nSummary:");
        $this->info("Total vehicles due: " . $vehicles->count());
        $this->info("Reminders sent: {$sent}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }

        return Command::SUCCESS;
    }
}
