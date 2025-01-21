<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'UID',
        'available_items',
        'used_items',
    ];
}
