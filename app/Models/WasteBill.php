<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'due_date',
        'amount',
        'ref',
    ];
}
