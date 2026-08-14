<?php

use Laravel\Mcp\Server\Facades\Mcp;

// Loaded by laravel/mcp under the /mcp prefix — this registers POST /mcp.
Mcp::web('/', \App\Mcp\Servers\MonicaServer::class)
    ->middleware([
        \App\Mcp\Http\Middleware\AdvertiseResourceMetadata::class,
        'auth:api,sanctum',
        'throttle:mcp',
    ]);
