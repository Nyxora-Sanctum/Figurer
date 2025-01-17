<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
        public function AIOutput(Request $request)
        {   
                $prompt = $request->input("prompt");
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-ms-model-mesh-model-name' => 'Llama-3.3-70B-Instruct',
                    'api-key' => '2NXq7H4hzhNXoY6HBlwMcCtlsmy748YwvEVjLavBzYlzApU2WtIkJQQJ99BAACHYHv6XJ3w3AAAAACOGEMtk',
                ])->post('https://ai-nyxhub333767215734065.services.ai.azure.com/models/chat/completions?api-version=2024-05-01-preview', [
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are portofolio maker. Only answer in this format:
                                {Name : Detailed Name,
                                Age : Detailed Age,
                                Location : Correct Location,
                                Overview : Detailed Overview,
                                Experience : Detailed Experience,
                                Education : Detailed Education,
                                Skills : Detailed Skills,
                                Projects : Detailed Projects,}
                                
                                Extract information from prompt. If the input is not complete and input detected not related with portofolio maker purpose please response with 'Error'. Write beautiful medium length portofolio for experience. If User providing not enough information, please type 'Need Information About: Variable'.",
                        ],
                        [
                            'role' => 'user',
                            'content' => $request['prompt']
                        ]

                    ],
                    'max_tokens' => 800,
                    'temperature' => 0.7,
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0,
                    'best_of' => 1,
                    'top_p' => 0.95,
                    'stop' => null,
                ]);

                $responseContent = $response->json();
                $messageContent = $responseContent['choices'][0]['message']['content'];
                return $messageContent;
            }
        }