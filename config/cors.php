<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'payment/*', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173', // ✅ আপনার বর্তমান ফ্রন্টএন্ড
        'http://127.0.0.1:5173', // সেইফটির জন্য
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Sanctum টোকেন ব্যবহারের জন্য এটি true থাকতেই হবে
];
