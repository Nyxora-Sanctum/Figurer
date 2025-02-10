<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Config;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
        public function AIOutput(Request $request)
        {       
                $config = Config::first();
                log::info($config);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-ms-model-mesh-model-name' => $config['ai_model'],
                    'api-key' => $config['ai_api_key'],
                ])->post($config['ai_endpoint'], [
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "YOU ARE CV MAKER ASSISTANT.",
                        ],
                        [
                            'role' => 'system',
                            'content' => "IMPORTANT: ONLY ANSWER WITH PROVIDED JSON FORMAT:" . $config['ai_output_format'],
                        ],
                        [
                            'role' => 'system',
                            'content' => "SUPPORTED LANGUAGES: " . $config['ai_supported_language'],
                        ],
                        [
                            'role' => 'system',
                            'content' => $config['ai_system_messages'],
                        ],
                        [
                            'role' => 'user',
                            'content' => $request['prompt']
                        ]

                    ],
                    'max_tokens' => $config['max_tokens'],
                    'temperature' => $config['temperature'],
                    'frequency_penalty' => $config['frequency_penalty'],
                    'presence_penalty' => $config['presence_penalty'],
                    'best_of' => $config['best_of'],
                    'top_p' => $config['top_p'],
                    'stop' => null,
                ]);

                $responseContent = $response->json();
                $messageContent = $responseContent['choices'][0]['message']['content'];
                return $messageContent;
    }

    public function getAIConfig(Request $request)
    {
        $config = Config::first();
        return $config;
    }

    public function updateAIConfig(Request $request)
    {
        $config = Config::first();

        $config->ai_model = $request->input('ai_model') ?? $config->ai_model;
        $config->ai_api_key = $request->input('ai_api_key') ?? $config->ai_api_key;
        $config->ai_endpoint = $request->input('ai_endpoint') ?? $config->ai_endpoint;
        $config->ai_output_format = $request->input('ai_output_format') ?? $config->ai_output_format;
        $config->ai_supported_language = $request->input('ai_supported_language') ?? $config->ai_supported_language;
        $config->ai_system_messages = $request->input('ai_system_messages') ?? $config->ai_system_messages;
        $config->max_tokens = $request->input('max_tokens') ?? $config->max_tokens;
        $config->temperature = $request->input('temperature') ?? $config->temperature;
        $config->frequency_penalty = $request->input('frequency_penalty') ?? $config->frequency_penalty;
        $config->presence_penalty = $request->input('presence_penalty') ?? $config->presence_penalty;
        $config->best_of = $request->input('best_of') ?? $config->best_of;
        $config->top_p = $request->input('top_p') ?? $config->top_p;

        $config->save();

        return response()->json(['message' => 'AI config updated successfully']);
    }
}