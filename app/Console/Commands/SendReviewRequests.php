<?php

namespace App\Console\Commands;

use App\Mail\ReviewRequest as ReviewRequestMail;
use App\Models\ReviewRequest;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReviewRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:send 
                            {--force : Send all pending reviews regardless of schedule}
                            {--limit=50 : Maximum number of reviews to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending review requests to customers';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService): int
    {
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        $query = ReviewRequest::with(['customer', 'jobCard.vehicle', 'jobCard.invoice'])
            ->where('status', 'pending');

        if (!$force) {
            $query->where('scheduled_for', '<=', now());
        }

        $reviewRequests = $query->limit($limit)->get();

        if ($reviewRequests->isEmpty()) {
            $this->info('No pending review requests found.');
            return self::SUCCESS;
        }

        $this->info("Found {$reviewRequests->count()} review requests to send...");

        $progressBar = $this->output->createProgressBar($reviewRequests->count());
        $progressBar->start();

        $sent = 0;
        $failed = 0;

        foreach ($reviewRequests as $reviewRequest) {
            try {
                $customer = $reviewRequest->customer;

                // Send email
                if ($customer->email && $customer->email_notifications) {
                    Mail::to($customer->email)->send(new ReviewRequestMail($reviewRequest));
                }

                // Send SMS if customer has phone and SMS enabled
                if ($customer->phone && $customer->sms_notifications) {
                    $message = "Hi {$customer->first_name}! Thanks for choosing us for your "
                        . "{$reviewRequest->jobCard->vehicle->registration}. "
                        . "We'd love your feedback! Leave a Google review: {$reviewRequest->review_link}";
                    
                    $smsService->send($customer->phone, $message);
                }

                $reviewRequest->markAsSent();
                $sent++;

            } catch (\Exception $e) {
                $this->error("Failed to send review request #{$reviewRequest->id}: {$e->getMessage()}");
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully sent: {$sent}");
        
        if ($failed > 0) {
            $this->warn("⚠️  Failed to send: {$failed}");
        }

        return self::SUCCESS;
    }
}
