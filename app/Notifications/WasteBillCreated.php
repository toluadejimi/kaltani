<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WasteBillCreated extends Notification
{
    use Queueable;

    public $bill;
    public $property;

    public function __construct($bill, $property)
    {
        $this->bill = $bill;
        $this->property = $property;
    }

    public function via($notifiable)
    {
        return ['mail', \App\Notifications\Channels\CustomSmsChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Waste Bill: {$this->bill->ref}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new waste bill has been generated for your property at: {$this->property->address}.")
            ->line("Amount: ₦" . number_format($this->bill->amount, 2))
            ->line("Reference: {$this->bill->ref}")
            ->line("Due Date: " . $this->bill->due_date->toFormattedDateString())
            ->action('View Bill', url("/bills/{$this->bill->ref}"))
            ->line('Thank you for helping keep Abia clean.');
    }

    public function toSms($notifiable)
    {
        return "Waste Bill {$this->bill->ref} for property at {$this->property->address}: ₦"
            . number_format($this->bill->amount, 2)
            . " due by " . $this->bill->due_date->format('M j, Y')
            . ". Ref: {$this->bill->ref}. Thank you for keeping Abia clean.";
    }
}
