<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Models\WasteBill;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AddMonthlyBills extends Command
{
    protected $signature = 'bills:add-monthly';
    protected $description = 'Add a bill for every customer at the beginning of the month';

    public function handle(MicrosoftGraphMailService $mailer)
    {

        function billingPeriodStringCarbon(Carbon $date): string {
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $formatWithOrdinal = function (Carbon $d) {
                $day = $d->day;
                $suffix = match (true) {
                    $day % 10 === 1 && $day % 100 !== 11 => 'st',
                    $day % 10 === 2 && $day % 100 !== 12 => 'nd',
                    $day % 10 === 3 && $day % 100 !== 13 => 'rd',
                    default => 'th',
                };
                return $d->format('F ') . $day . $suffix;
            };

            if ($start->isSameMonth($end)) {
                $month = $start->format('F ');
                return $month . $start->day . match (true) {
                        $start->day % 10 === 1 && $start->day % 100 !== 11 => 'st',
                        $start->day % 10 === 2 && $start->day % 100 !== 12 => 'nd',
                        $start->day % 10 === 3 && $start->day % 100 !== 13 => 'rd',
                        default => 'th',
                    } . ' - ' . $end->day . match (true) {
                        $end->day % 10 === 1 && $end->day % 100 !== 11 => 'st',
                        $end->day % 10 === 2 && $end->day % 100 !== 12 => 'nd',
                        $end->day % 10 === 3 && $end->day % 100 !== 13 => 'rd',
                        default => 'th',
                    };
            }

            return $formatWithOrdinal($start) . ' - ' . $formatWithOrdinal($end);
        }


        User::with(['userProperties.property'])->chunk(100, function ($users) use ($mailer) {
            foreach ($users as $user) {

                if ($user->userProperties->isEmpty()) {
                    Log::warning("User {$user->id} has no properties; skipping.");
                    $this->warn("User {$user->id} has no properties; skipping.");
                    continue;
                }

                foreach ($user->userProperties as $userProperty) {
                    $prop = $userProperty->property;
                    if (! $prop) {
                        Log::warning("UserProperty {$userProperty->id} has no linked property; skipping.");
                        $this->warn("UserProperty {$userProperty->id} has no linked property; skipping.");
                        continue;
                    }

                    if (! isset($prop->amount)) {
                        Log::warning("Property {$prop->id} has no amount; skipping.");
                        $this->warn("Property {$prop->id} has no amount; skipping.");
                        continue;
                    }

                    // Generate unique ref
                    $ref = null;
                    for ($i = 0; $i < 5; $i++) {
                        $candidate = 'BILL' . str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
                        if (! WasteBill::where('ref', $candidate)->exists()) {
                            $ref = $candidate;
                            break;
                        }
                    }
                    if (! $ref) {
                        Log::warning("Could not generate unique ref for userProperty {$userProperty->id}; skipping.");
                        $this->error("Could not generate unique ref for userProperty {$userProperty->id}; skipping.");
                        continue;
                    }

                    // Avoid duplicate bill for this month
                    $existing = WasteBill::where('user_id', $user->id)
                        ->where('user_property_id', $userProperty->id)
                        ->whereYear('due_date', now()->year)
                        ->whereMonth('due_date', now()->month)
                        ->first();

                    if ($existing) {
                        Log::warning("Bill already exists for user {$user->id}, userProperty {$userProperty->id} this month; skipping.");
                        $this->warn("Bill already exists for user {$user->id}, userProperty {$userProperty->id} this month; skipping.");
                        continue;
                    }

                    try {
                        $bill = WasteBill::create([
                            'user_id' => $user->id,
                            'user_property_id' => $userProperty->id,
                            'amount' => $prop->amount,
                            'ref' => $ref,
                            'due_date' => now()->endOfMonth(),
                        ]);




                        $data = [
                            'fromsender' => 'info@kaltani.com',
                            'toreceiver' => $user->email,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'first_name' => $user->first_name,
                            'customer_id' => $user->customer_id,
                            'period' => billingPeriodStringCarbon(Carbon::now()),
                            'bill_no' => $ref,
                            'amount' => $prop->amount,
                            'url' => url('')."/api/get-bill-pdf?ref=$ref",
                            'pay_url' => url('')."/pay-bill?user_id=$user->id&amount=$prop->amount&email=$user->email&ref=$ref",
                        ];

                        $subject = "Your Monthly Trash Bash Bill – ".date('F-Y');
                        $view = 'monthlybill';

                        $mailer->SendEmailView($user->email, $subject, $view, $data);
//
//                        // dispatch SMS here if you have a service or notification

                        Log::info("Created bill {$bill->ref} for user {$user->id}, userProperty {$userProperty->id} and notified.");
                    } catch (\Illuminate\Database\QueryException $e) {
                        Log::error("DB error for user {$user->id}, userProperty {$userProperty->id}: " . $e->getMessage());
                        $this->error("DB error for user {$user->id}, userProperty {$userProperty->id}: " . $e->getMessage());
                    }
                }
            }
        });

        $this->info('Monthly bills added successfully.');
    }

}
