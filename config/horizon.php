<?php

// config/horizon.php

return [
    'use' => 'default',

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
        'redis:notifications' => 60,
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'failed' => 7 * 24,
    ],

    'fast_termination' => false,

    'memory_limit' => 64,

    'environments' => [
        'production' => [
            'supervisor-notifications' => [
                'connection' => 'redis-notifications',
                'queue' => ['notifications-urgent', 'notifications-high', 'notifications-medium', 'notifications-low'],
                'balance' => 'simple',
                'processes' => 8,
                'tries' => 3,
                'timeout' => 60,
                'nice' => 0,
            ],
            'supervisor-batch' => [
                'connection' => 'redis-notifications',
                'queue' => ['notifications-batch-urgent', 'notifications-batch-high', 'notifications-batch-medium', 'notifications-batch-low'],
                'balance' => 'simple',
                'processes' => 4,
                'tries' => 3,
                'timeout' => 300,
                'nice' => 0,
            ],
            'supervisor-topic' => [
                'connection' => 'redis-notifications',
                'queue' => ['notifications-topic'],
                'balance' => 'simple',
                'processes' => 2,
                'tries' => 3,
                'timeout' => 60,
                'nice' => 0,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => 'redis-notifications',
                'queue' => [
                    'notifications-urgent',
                    'notifications-high',
                    'notifications-medium',
                    'notifications-low',
                    'notifications-batch-urgent',
                    'notifications-batch-high',
                    'notifications-batch-medium',
                    'notifications-batch-low',
                    'notifications-topic'
                ],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 3,
                'timeout' => 300,  // 5 minutes for local development
                'nice' => 0,
            ],
        ],
    ],
];
