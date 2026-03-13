<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Reminder;
use App\Mail\AppointmentReminder;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders {--hours=24 : Hours before appointment} {--sms : Also send SMS}';
    protected $description = 'Send email/SMS reminders for upcoming appointments';

    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $sendSms = $this->option('sms');
        $reminderTime = Carbon::now()->addHours($hours);

        $this->info("Looking for appointments in the next {$hours} hours...");

        $appointments = Appointment::with(['customer', 'vehicle'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereBetween('scheduled_date', [
                Carbon::now(),
                $reminderTime
            ])
            ->whereHas('customer', function($query) {
                $query->whereNotNull('email');
            })
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments found requiring reminders.');
            return Command::SUCCESS;
        }

        $this->info("Found {$appointments->count()} appointment(s) to remind.");

        $emailsSent = 0;
        $smsSent = 0;
        $errors = 0;

        foreach ($appointments as $appointment) {
            // Check if reminder already sent
            $alreadySent = Reminder::where('customer_id', $appointment->customer_id)
                ->where('type', 'appointment')
                ->where('sent_at', '>=', Carbon::now()->subDay())
                ->exists();

            if ($alreadySent) {
                $this->line("⊘ Skipping {$appointment->reference_number} - reminder already sent");
                continue;
            }

            try {
                // Send email reminder
                if ($appointment->customer->email) {
                    Mail::to($appointment->customer->email)
                        ->send(new AppointmentReminder($appointment));
                    $emailsSent++;
                    
                    $this->line("✓ Email sent to {$appointment->customer->full_name} ({$appointment->reference_number})");
                    
                    // Log reminder
                    Reminder::create([
                        'customer_id' => $appointment->customer_id,
                        'vehicle_id' => $appointment->vehicle_id,
                        'type' => 'appointment',
                        'due_date' => $appointment->scheduled_date,
                        'status' => 'sent',
                        'sent_at' => now(),
                        'message' => 'Appointment reminder sent',
                    ]);
                }

                // Send SMS if requested
                if ($sendSms && $appointment->customer->phone) {
                    $message = "Reminder: Appointment at Doyen Auto Services on " . 
                               Carbon::parse($appointment->scheduled_date)->format('d/m/Y \a\t H:i') . 
                               ". Ref: {$appointment->reference_number}";
                    
                    if ($this->smsService->send($appointment->customer->phone, $message)) {
                        $smsSent++;
                        $this->line("✓ SMS sent to {$appointment->customer->phone}");
                    }
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Failed for {$appointment->reference_number}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("========== SUMMARY ==========");
        $this->info("Appointments checked: " . $appointments->count());
        $this->info("Emails sent: {$emailsSent}");
        if ($sendSms) {
            $this->info("SMS sent: {$smsSent}");
        }
        if ($errors > 0) {
            $this->warn("Errors: {$errors}");
        }

        return Command::SUCCESS;
    }
}
