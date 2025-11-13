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
    // Redis set name used to index cached message IDs (optional, used to avoid KEYS)
    'index' => env('MESSAGE_CACHE_INDEX', 'message_index'),
];
