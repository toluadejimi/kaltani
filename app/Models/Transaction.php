<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'user_type',
        'create_at',
        'updated_at',
        'account_no',
        'trans_id'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
