<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'kg',
        'user_id',
        'driver_id',
        'image'

    ];
}
