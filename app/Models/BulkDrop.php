<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkDrop extends Model
{
    use HasFactory;

    protected $casts = [
        'items' => 'json'
    ];

}
