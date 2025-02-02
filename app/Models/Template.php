<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'template';

    protected $fillable = [
        'name',
        'unique_cv_id',
        'price',
        'template-link',
        'template-preview',
        'created_at',
        'updated_at',
    ];
}
