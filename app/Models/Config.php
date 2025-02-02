<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'Config';

    protected $fillable = [
        'ai-model',
        'ai-api-key',
        'ai-endpoint',
        'ai-output-format',
        'ai-supported-language',
        'ai-system-messages',
        'max-tokens',
        'temperature',
        'frequency-penalty',
        'presence-penalty',
        'best-of',
        'top-p',
    ];

}