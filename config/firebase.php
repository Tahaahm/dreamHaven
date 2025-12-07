<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID', 'dream-haven-31029'),

    'service_account_path' => env('FIREBASE_SERVICE_ACCOUNT_PATH')
        ? base_path(env('FIREBASE_SERVICE_ACCOUNT_PATH'))
        : null,

    'api_key' => env('FIREBASE_API_KEY'),

    // Add this line
    'firestore_enabled' => env('FIRESTORE_ENABLED', false),
];
