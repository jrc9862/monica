<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MCP payload debugging
    |--------------------------------------------------------------------------
    |
    | When enabled, the raw JSON-RPC request and response bodies for /mcp are
    | written to the log. laravel/mcp returns handler exceptions as JSON-RPC
    | errors with HTTP 200 and does not report them, so this is the only way to
    | see why a request failed. Bodies may contain vault data — keep this off
    | outside of active debugging.
    |
    */

    'debug_payloads' => env('MCP_DEBUG_PAYLOADS', false),

];
