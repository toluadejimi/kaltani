<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BulkDrop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'long',
        'lat' ,
        'items' ,
        'address',
        'ref',
        'images'
    ];

    protected $casts = [
        'items' => 'json'
    ];

}
