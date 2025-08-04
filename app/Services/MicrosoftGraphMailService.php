<?php

namespace App\Services;

use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\GraphApiSetting;

class MicrosoftGraphMailService
{
    protected $settings;
    protected $token;

    public function __construct()
    {
        $this->settings = Setting::first();

        if (!$this->settings) {
            throw new \Exception("Microsoft Graph API settings not found.");
        }

        $this->token = $this->getAccessToken();
    }

    protected function getAccessToken()
    {

        $cacheKey = 'graph_api_token_' . md5($this->settings->client_id);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }


        $response = Http::asForm()->post("https://login.microsoftonline.com/{$this->settings->tenant_id}/oauth2/v2.0/token", [
            'client_id'     => $this->settings->client_id,
            'client_secret' => $this->settings->client_secret,
            'scope'         => 'https://graph.microsoft.com/.default',
            'grant_type'    => 'client_credentials',
        ]);

        if (!$response->successful()) {
            throw new \Exception("Unable to get access token: " . $response->body());
        }

        $token = $response->json()['access_token'];

        // Cache for 58 minutes (token is valid for 60 mins)
        Cache::put($cacheKey, $token, now()->addMinutes(58));

        return $token;
    }

    public function sendEmail($toEmail, $subject, array $invoiceData)
    {
        $pdf = Pdf::loadView('invoices.invoice', ['invoice' => $invoiceData]);
        $pdfContent = $pdf->output();

        $attachment = [
            '@odata.type' => '#microsoft.graph.fileAttachment',
            'name' => 'Invoice.pdf',
            'contentBytes' => base64_encode($pdfContent),
            'contentType' => 'application/pdf',
        ];

        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'Text',
                    'content' => "Dear Customer,\n\nThank you for being a valued part of the Trash Bash community.\n\nPlease find your invoice attached for your recent transaction. Kindly review the details at your convenience. If you have any questions or need further assistance, feel free to reach out.\n\nWe appreciate your continued support.\n\nBest regards,\nThe Trash Bash Team",
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $toEmail,
                        ],
                    ],
                ],
                'attachments' => [$attachment],
            ]
        ];


        $senderEmail = 'info@kaltani.com';
        $response = Http::withToken($this->token)
            ->post("https://graph.microsoft.com/v1.0/users/{$senderEmail}/sendMail", $payload);

        if (!$response->successful()) {
            throw new \Exception("Failed to send email with PDF: " . $response->body());
        }

        return true;
    }

    public function SendEmailView($toEmail, $subject, $view, array $Data)
    {


        $htmlBody = view($view, ['data1' => $Data])->render();
        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $htmlBody,
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $toEmail,
                        ],
                    ],
                ],
            ],
        ];

        $senderEmail = 'info@kaltani.com';
        $response = Http::withToken($this->token)
            ->post("https://graph.microsoft.com/v1.0/users/{$senderEmail}/sendMail", $payload);

        if (!$response->successful()) {
            throw new \Exception("Failed to send email: " . $response->body());
        }

        return true;
    }


}
