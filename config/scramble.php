<?php

return [
    /*
     * API Documentation Path
     */
    'path' => 'docs/api',

    /*
     * API Info
     */
    'info' => [
        'title' => 'AI School API',
        'description' => 'API Documentation for AI School Management System',
        'version' => '1.0.0',
    ],

    /*
     * Servers
     */
    'servers' => null,

    /*
     * API Domain - null means current domain
     */
    'api_domain' => null,

    /*
     * Routes to document
     */
    'api' => [
        'routes' => [
            [
                'match' => [
                    'prefix' => 'api/v1/*',
                ],
            ],
        ],
    ],
];
