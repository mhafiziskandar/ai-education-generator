<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),
    'model' => env('OPENAI_MODEL', 'gpt-4'),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
];