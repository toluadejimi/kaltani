<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DailyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Next Day Update';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dd('hello wordl');
    }
}
