<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WhatsAppConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Handles inbound messages from Twilio's WhatsApp webhook.
 *
 * Twilio Webhook URL: POST /webhooks/whatsapp/inbound
 * (exclude from CSRF in VerifyCsrfToken)
 */
class WhatsAppWebhookController extends Controller
{
    public function inbound(Request $request)
    {
        Log::info('WhatsApp inbound webhook', $request->all());

        try {
            $from    = $this->sanitizePhone($request->input('From', ''));
            $body    = $request->input('Body', '');
            $sid     = $request->input('MessageSid');
            $mediaUrl  = $request->input('MediaUrl0');
            $mediaType = $request->input('MediaContentType0');

            if (empty($from)) {
                return response('Bad Request', 400);
            }

            // Try to match an existing customer by phone
            $customer = Customer::where('phone', $from)
                ->orWhere('phone', '+' . ltrim($from, '+'))
                ->first();

            $conversation = WhatsAppConversation::firstOrCreate(
                ['customer_phone' => $from],
                [
                    'customer_name' => $customer ? $customer->name : $from,
                    'customer_id'   => $customer?->id,
                    'status'        => 'open',
                ]
            );

            // Update customer_id if we found one and it wasn't set
            if ($customer && !$conversation->customer_id) {
                $conversation->update([
                    'customer_id'   => $customer->id,
                    'customer_name' => $customer->name,
                ]);
            }

            $conversation->addInboundMessage($body, $sid, $mediaUrl, $mediaType);

        } catch (\Throwable $e) {
            Log::error('WhatsApp webhook error: ' . $e->getMessage());
        }

        // Twilio expects a 200 TwiML response (empty is fine)
        return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
            ->header('Content-Type', 'text/xml');
    }

    /*
    |--------------------------------------------------------------------------
    | Twilio message status callback
    |--------------------------------------------------------------------------
    */
    public function statusCallback(Request $request)
    {
        $sid    = $request->input('MessageSid');
        $status = $request->input('MessageStatus');

        if ($sid && $status) {
            \App\Models\WhatsAppMessage::where('twilio_sid', $sid)->update(['status' => $status]);
        }

        return response('OK', 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function sanitizePhone(string $raw): string
    {
        // Twilio sends "whatsapp:+447123456789"
        $phone = str_replace('whatsapp:', '', $raw);
        return preg_replace('/[^+0-9]/', '', $phone);
    }
}
