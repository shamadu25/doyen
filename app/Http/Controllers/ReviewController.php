<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\ReviewRequest;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Track review link click and redirect to Google review page
     */
    public function track($jobCardId)
    {
        $jobCard = JobCard::findOrFail($jobCardId);
        
        // Mark review request as clicked
        $reviewRequest = ReviewRequest::where('job_card_id', $jobCardId)->first();
        if ($reviewRequest) {
            $reviewRequest->markAsClicked();
        }

        // Get Google review URL from config
        $googleReviewUrl = config('services.google.review_link');
        
        if (!$googleReviewUrl || $googleReviewUrl === 'https://g.page/r/YOUR_PLACE_ID/review') {
            // Fallback to generic Google search for business reviews
            $businessName = config('app.name');
            $googleReviewUrl = "https://www.google.com/search?q=" . urlencode($businessName . " reviews");
        }

        return redirect()->away($googleReviewUrl);
    }

    /**
     * Webhook to mark review as completed (if using Google My Business API)
     */
    public function completed(Request $request, $jobCardId)
    {
        $reviewRequest = ReviewRequest::where('job_card_id', $jobCardId)->first();
        
        if ($reviewRequest) {
            $reviewRequest->markAsReviewed();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Display review statistics in admin dashboard.
     */
    public function stats()
    {
        $stats = [
            'total_sent' => ReviewRequest::whereNotNull('sent_at')->count(),
            'total_clicked' => ReviewRequest::whereNotNull('clicked_at')->count(),
            'total_reviewed' => ReviewRequest::whereNotNull('reviewed_at')->count(),
            'pending' => ReviewRequest::whereNull('sent_at')->count(),
            'click_rate' => 0,
            'review_rate' => 0,
        ];

        if ($stats['total_sent'] > 0) {
            $stats['click_rate'] = round(($stats['total_clicked'] / $stats['total_sent']) * 100, 1);
            $stats['review_rate'] = round(($stats['total_reviewed'] / $stats['total_sent']) * 100, 1);
        }

        return response()->json($stats);
    }
}
