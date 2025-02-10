<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'Config';

    protected $fillable = [
        'ai_model',
        'ai_api-key',
        'ai_endpoint',
        'ai_output_format',
        'ai_supported_language',
        'ai_system_messages',
        'max_tokens',
        'temperature',
        'frequency_penalty',
        'presence_penalty',
        'best_of',
        'top_p',
    ];

}