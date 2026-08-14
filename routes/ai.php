<?php

use Laravel\Mcp\Server\Facades\Mcp;

// Loaded by laravel/mcp under the /mcp prefix — this registers POST /mcp.
Mcp::web('/', \App\Mcp\Servers\MonicaServer::class)
    ->middleware([
        \App\Mcp\Http\Middleware\LogMcpPayloads::class,
        \App\Mcp\Http\Middleware\AdvertiseResourceMetadata::class,
        // Sanctum must come first: Passport's TokenGuard throws an
        // OAuthServerException on a non-JWT bearer token rather than returning
        // null, which aborts the guard chain before Sanctum ever sees a valid
        // personal access token. Sanctum returns null cleanly on a Passport JWT,
        // so this order authenticates both.
        'auth:sanctum,api',
        'throttle:mcp',
    ]);
