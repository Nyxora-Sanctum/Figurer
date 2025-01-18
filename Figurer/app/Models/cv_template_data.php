<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cv_template_data extends Model
{
    protected $table = 'cv_template_data';

    protected $fillable = [
        'name',
        'id_number',
        'price',
        'link',
    ];
}
