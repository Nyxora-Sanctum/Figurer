<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'unique_cv_id',
        'invoice_id',
        'order_id',
        'status',
        'created_at',
        'updated_at',
    ];
}

