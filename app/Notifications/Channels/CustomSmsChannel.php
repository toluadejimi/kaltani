<?php

// app/Notifications/Channels/CustomSmsChannel.php
namespace App\Notifications\Channels;

use App\Services\TermiiService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomSmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        $to = $notifiable->phone;

        if (! $to) {
            Log::warning("No phone number for user {$notifiable->id}; skipping SMS.");
            return;
        }

        try {

//            $smsService = new TermiiService();
//            $phone_no = preg_replace('/^\[?0\]?/', '', $to);
//            $phone_n = "+234" . $phone_no;
//            $response = $smsService->sendSms($phone_n, $message);
//
//            if (!isset($response['code']) || $response['code'] !== 'ok') {
//
//                $message =  "Termi Error=====>". $response;
//                LOG::error($message);
//
//            }


        } catch (\Exception $e) {
            Log::error("SMS exception for user {$notifiable->id}: " . $e->getMessage());
        }
    }
}
