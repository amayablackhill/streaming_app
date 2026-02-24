<?php

return [
    'features' => [
        'admin_writes' => env('ADMIN_WRITES_ENABLED', true),
        'tmdb_import' => env('TMDB_IMPORT_ENABLED', true),
    ],
];
