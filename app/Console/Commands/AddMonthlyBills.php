<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Models\WasteBill;
use Illuminate\Console\Command;

class AddMonthlyBills extends Command
{
    protected $signature = 'bills:add-monthly';
    protected $description = 'Add a bill for every customer at the beginning of the month';

    public function handle()
    {

        $bill_amount = Setting::where('id', 1)->first()->bill_amount;
        $customers = User::all();
        $ref = "BILL".random_int(0000000000, 9999999999);
        foreach ($customers as $customer) {
                WasteBill::create([
                'user_id' => $customer->id,
                'amount' => $bill_amount,
                'ref' => $ref,
                'due_date' => now()->endOfMonth(),
            ]);
        }

        $this->info('Monthly bills added successfully.');
    }
}
