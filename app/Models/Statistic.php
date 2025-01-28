<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $table = 'statistic';

    protected $fillable = [
         'page_views',
        'registered_users',
        'active_users',
        'sales',
        'api_requests',
    ];
}