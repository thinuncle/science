<?php
return [
    'displayErrorDetails' => false, // set to false in production
    'addContentLengthHeader' => false,

    // OAuth 2 configuration
    'oauth2' => [
        'use_jwt_bearer_tokens' => true,
    ],

    // Database adapter
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=oic_educations',
        'user' => 'homestead',
        'pass' => 'secret',
    ],

    // Monolog
    'logger' => [
        'name' => 'science-api',
        // uncomment 'path' setting to log to file rather than the error log
        // 'path' => __DIR__ . '/../var/app.log',
    ],
    'settings' => [
        'upload' => __DIR__ . '/../public/upload/'
    ]
];
