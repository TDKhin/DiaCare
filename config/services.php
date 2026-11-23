<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ai' => [
        // Separate credentials let the text assistant and photo analysis use
        // different providers, quotas, and models. The legacy values remain
        // as fallbacks so existing installations continue to work.
        'text' => [
            'provider' => env('AI_TEXT_PROVIDER') ?: env('AI_PROVIDER', 'gemini'),
            'base_url' => env('AI_TEXT_BASE_URL') ?: env('AI_API_BASE_URL', 'https://api.openai.com/v1'),
            'key' => env('GROQ_TEXT_API_KEY') ?: env('AI_TEXT_API_KEY') ?: env('CHAT_API_KEY') ?: env('GEMINI_CHAT_API_KEY') ?: env('GEMINI_API_KEY') ?: env('AI_API_KEY') ?: env('GOOGLE_API_KEY'),
            'model' => env('AI_TEXT_MODEL') ?: env('CHAT_MODEL') ?: env('GEMINI_CHAT_MODEL') ?: env('GEMINI_MODEL', 'gemini-2.5-flash'),
        ],
        'vision' => [
            'provider' => env('AI_VISION_PROVIDER') ?: env('AI_PROVIDER', 'gemini'),
            'base_url' => env('AI_VISION_BASE_URL') ?: env('AI_API_BASE_URL', 'https://api.openai.com/v1'),
            'key' => env('GROQ_VISION_API_KEY') ?: env('AI_VISION_API_KEY') ?: env('VISION_API_KEY') ?: env('GEMINI_API_KEY') ?: env('AI_API_KEY') ?: env('GOOGLE_API_KEY'),
            'model' => env('AI_VISION_MODEL') ?: env('VISION_MODEL') ?: env('GEMINI_MODEL', 'gemini-2.5-flash'),
        ],
    ],

];
