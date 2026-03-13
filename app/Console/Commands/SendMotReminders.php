<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Mail\MotReminderMail;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendMotReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mot:send-reminders 
                            {--days=30 : Number of days before expiry to send reminder}
                            {--sms : Also send SMS reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send MOT expiry reminders to customers';

    protected $smsService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $sendSms = $this->option('sms');

        $this->info("Checking for vehicles with MOT expiring in {$days} days...");

        // Get vehicles due for MOT
        $targetDate = Carbon::now()->addDays($days)->startOfDay();
        
        $vehicles = Vehicle::with('customer')
            ->whereNotNull('mot_due_date')
            ->whereDate('mot_due_date', $targetDate)
            ->where('is_active', true)
            ->get();

        if ($vehicles->isEmpty()) {
            $this->info('No vehicles found with MOT expiring in ' . $days . ' days.');
            return 0;
        }

        $this->info("Found {$vehicles->count()} vehicle(s) to remind.");

        $emailsSent = 0;
        $smsSent = 0;
        $errors = 0;

        foreach ($vehicles as $vehicle) {
            try {
                // Send email reminder
                if ($vehicle->customer->email) {
                    Mail::to($vehicle->customer->email)
                        ->queue(new MotReminderMail($vehicle));
                    $emailsSent++;
                    
                    $this->line("✓ Email queued for {$vehicle->registration_number} ({$vehicle->customer->email})");
                }

                // Send SMS if requested and phone available
                if ($sendSms && $vehicle->customer->phone) {
                    $this->smsService = new SmsService();
                    
                    $message = "MOT Reminder: Your {$vehicle->make} {$vehicle->model} ({$vehicle->registration_number}) MOT expires on {$vehicle->mot_due_date->format('d/m/Y')}. Book now: " . config('app.url');
                    
                    $result = $this->smsService->send($vehicle->customer->phone, $message);
                    
                    if ($result) {
                        $smsSent++;
                        $this->line("✓ SMS sent to {$vehicle->customer->phone}");
                    }
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Failed for {$vehicle->registration_number}: " . $e->getMessage());
            }
        }

        // Summary
        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Vehicles found: {$vehicles->count()}");
        $this->info("Emails queued: {$emailsSent}");
        if ($sendSms) {
            $this->info("SMS sent: {$smsSent}");
        }
        if ($errors > 0) {
            $this->warn("Errors: {$errors}");
        }

        return 0;
    }
}
