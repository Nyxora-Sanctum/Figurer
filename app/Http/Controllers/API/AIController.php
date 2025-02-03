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
                    'x-ms-model-mesh-model-name' => $config['ai-model'],
                    'api-key' => $config['ai-api-key'],
                ])->post($config['ai-endpoint'], [
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "YOU ARE CV MAKER ASSISTANT.",
                        ],
                        [
                            'role' => 'system',
                            'content' => "IMPORTANT: ONLY ANSWER WITH PROVIDED JSON FORMAT:" . $config['ai-output-format'],
                        ],
                        [
                            'role' => 'system',
                            'content' => "SUPPORTED LANGUAGES: " . $config['ai-supported-language'],
                        ],
                        [
                            'role' => 'system',
                            'content' => $config['ai-system-messages'],
                        ],
                        [
                            'role' => 'user',
                            'content' => $request['prompt']
                        ]

                    ],
                    'max_tokens' => $config['max-tokens'],
                    'temperature' => $config['temperature'],
                    'frequency_penalty' => $config['frequency-penalty'],
                    'presence_penalty' => $config['presence-penalty'],
                    'best_of' => $config['best-of'],
                    'top_p' => $config['top-p'],
                    'stop' => null,
                ]);

                $responseContent = $response->json();
                $messageContent = $responseContent['choices'][0]['message']['content'];
                return $messageContent;
            }
}