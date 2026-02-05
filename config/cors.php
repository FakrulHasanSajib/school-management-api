<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // ✅ payment/* রুটটি এখানে যুক্ত করা হয়েছে যাতে পেমেন্ট রিকোয়েস্ট আটকানো না হয়
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'payment/*'],

    'allowed_methods' => ['*'],

    // ✅ '*' এর বদলে নির্দিষ্ট পোর্ট দেওয়া হলো। এটি Network Error সমাধান করবে।
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ক্রেডেনশিয়াল ট্রু থাকলে অরিজিন '*' হতে পারবে না

];
