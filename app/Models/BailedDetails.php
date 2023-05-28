<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BailedDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'Clean_Clear',
        'Green_Colour',
        'Others',
        'Trash',
        'Caps',
        'hdpe',
        'ldpe',
        'black',
        'brown',
        'ldpe',
        'black'
    ];
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
