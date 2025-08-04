<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TermiiService
{
    protected $apiKey;
    protected $senderId;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('TERMII_API_KEY');
        $this->senderId = env('TERMII_SENDER_ID');
        $this->baseUrl = env('TERMII_BASE_URL');
    }

    public function sendSms($to, $message)
    {
        $payload = [
            'api_key' => $this->apiKey,
            'to' => $to,
            'from' => $this->senderId,
            'sms' => $message,
            'type' => 'plain', // Use 'plain' for normal SMS
            'channel' => 'dnd' // Options: dnd, whatsapp, generic
        ];

        $response = Http::post($this->baseUrl, $payload);

        return $response->json();
    }
}
