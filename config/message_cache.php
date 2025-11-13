<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Message cache key prefix
    |--------------------------------------------------------------------------
    |
    | Prefix used when composing message keys in the cache. Keep this in sync
    | between `MessageCacheService` and any code that searches Redis keys.
    |
    */
    'prefix' => env('MESSAGE_CACHE_PREFIX', 'myapp_db_message:'),
];
