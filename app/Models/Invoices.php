<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    protected $table = 'invoices';
    protected $fillable = [
        'username',
        'invoice_id',
        'order_id',
        'status',
        'amount',
        'item_id',
    ];
}
