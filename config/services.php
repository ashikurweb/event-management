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

    /*
    |--------------------------------------------------------------------------
    | Social Authentication Services
    |--------------------------------------------------------------------------
    |
    | Configuration for social login providers (Google, Facebook, GitHub)
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/auth/facebook/callback'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI', env('APP_URL') . '/auth/github/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API used by AI Assistant
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Groq AI Configuration (FREE TIER AVAILABLE)
    |--------------------------------------------------------------------------
    |
    | Groq provides fast AI inference with a generous free tier.
    | Get your free API key: https://console.groq.com/keys
    |
    */

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'enabled' => env('GROQ_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Qwen 2.5 Coder Configuration (BEST FOR CODING)
    |--------------------------------------------------------------------------
    |
    | Qwen 2.5 Coder is specialized for code generation, SQL, migrations,
    | REST API development, and database design.
    | Recommended for: Laravel migrations, controllers, SQL queries, APIs
    |
    | Get API key from:
    | - OpenRouter: https://openrouter.ai/ (Recommended)
    | - Hugging Face: https://huggingface.co/Qwen/Qwen2.5-Coder-32B-Instruct
    |
    */

    'qwen' => [
        'api_key' => env('QWEN_API_KEY'),
        'api_url' => env('QWEN_API_URL', 'https://openrouter.ai/api/v1/chat/completions'),
        'model' => env('QWEN_MODEL', 'qwen/qwen-2.5-coder-32b-instruct'),
        'enabled' => env('QWEN_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | DeepSeek R1 Configuration (BEST FOR REASONING)
    |--------------------------------------------------------------------------
    |
    | DeepSeek R1 provides GPT-4 level reasoning for complex problems,
    | database design, event-driven architecture, and system design.
    | Recommended for: Complex reasoning, architecture, deep analysis
    |
    | Get API key from:
    | - DeepSeek Platform: https://platform.deepseek.com/
    | - OpenRouter: https://openrouter.ai/ (Alternative)
    |
    */

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'api_url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-r1-14b'),
        'enabled' => env('DEEPSEEK_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter Configuration (SUPPORTS BOTH MODELS)
    |--------------------------------------------------------------------------
    |
    | OpenRouter provides access to multiple AI models including
    | Qwen 2.5 Coder and DeepSeek R1 through a single API.
    | Recommended for: Easy access to both models
    |
    | Get API key from: https://openrouter.ai/
    |
    */

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'api_url' => env('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions'),
        'model' => env('OPENROUTER_MODEL', 'qwen/qwen-2.5-coder-32b-instruct'),
        'enabled' => env('OPENROUTER_ENABLED', false),
    ],

];
