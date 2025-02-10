<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use App\Models\Config;
use Illuminate\Support\Facades\Hash;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ai_output_format= json_encode([
            "name" => "",
            "job" => "",
            "contact" => "",
            "address" => "",
            "education" => [
                [
                    "school" => "",
                    "degree" => "",
                    "major" => "",
                    "description" => "",
                    "start" => "",
                    "end" => ""
                ]
            ],
            "experiences" => [
                [
                    "place" => "",
                    "experience" => "",
                    "description" => "",
                    "start" => "",
                    "end" => ""
                ]
            ],
            "skills" => [
                [
                    "skill_name" => "",
                    "subskill" => [
                        [
                            "subskill_name" => ""
                        ]
                    ]
                ]
            ]
        ]);

        Config::create([
            'ai_model' => 'Llama-3.3-70B-Instruct',
            'ai_api_key' => '2NXq7H4hzhNXoY6HBlwMcCtlsmy748YwvEVjLavBzYlzApU2WtIkJQQJ99BAACHYHv6XJ3w3AAAAACOGEMtk',
            'ai_endpoint' => 'https://ai-nyxhub333767215734065.services.ai.azure.com/models/chat/completions?api-version=2024-05-01-preview',
            'ai_output_format' => $ai_output_format,
            'ai_supported_language' => json_encode(['english', 'indonesian']),
            'ai_system_messages' => json_encode(['message' => 'Empty data if information isn\'t provided by user.']), // Fixed this!
            'max_tokens' => 800,
            'temperature' => 0.7,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
            'best_of' => 1,
            'top_p' => 0.95,
        ]);
    }
}
