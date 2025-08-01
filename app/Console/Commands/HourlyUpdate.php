<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Greeting;
use Mail;
use App\Models\DropOff;


class HourlyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hour:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an hourly email to all the users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // $user_id = array(78, 117, 100);
        $user_id = DropOff::where('status', 0)
        ->get('user_id');


       $user = User::whereIn('id', $user_id)
        ->get('email');


        $customer_gender = User::whereIn('id', $user_id)
        ->get('gender');

        $age = User::whereIn('id', $user_id)
        ->get('age');



        foreach ($user as $a)

        if($customer_gender  == 'Male' && $age < 45 ){

            $greeting = Greeting::where('title', '1hrMale' )
            ->first()->gender;
        }else{

            $greeting = Greeting::where('title', '1hrFemale' )
            ->first()->gender;

        }

        //send email to sender
        $data = array(
            'fromsender' => 'notify@app.cardy4u.com', 'Trash Bash',
            'subject' => "Drop Off Reminder",
            'toreceiver' => $a->email,
            'greeting' => $greeting,

        );



        Mail::send('1hrdropoffemail', ["data1" => $data], function ($message) use ($data) {
            $message->from($data['fromsender']);
            $message->to($data['toreceiver']);
            $message->subject($data['subject']);

        });



        $this->info('Email sent successfully');
    }
}
