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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'face_recognition' => [
        'url' => env('FACE_RECOGNITION_API_URL', 'http://localhost:5000'),
        'key' => env('FACE_RECOGNITION_API_KEY', '5a16d8e3-61a7-40d6-8841-8eeadc653395'),
        'storage_path' => env('FACE_RECOGNITION_STORAGE_PATH', 'face_images'),
        'max_image_size' => env('FACE_RECOGNITION_MAX_IMAGE_SIZE', 5* 1024), // KB
    ],

    'qrcode' => [
        'expiry_time' => env('QRCODE_EXPIRY_TIME', 30), // minutes
        'size' => env('QRCODE_SIZE', 300), // pixels
    ],

];
