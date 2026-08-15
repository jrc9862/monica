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

    /*
    |--------------------------------------------------------------------------
    | Disabled tools
    |--------------------------------------------------------------------------
    |
    | Comma-separated tool names (as advertised over MCP, e.g. "delete-note")
    | to withhold from tools/list and refuse on tools/call. The destructive
    | tools are disabled by default: deleting a record through chat is rarely
    | wanted and cannot be undone. Set MCP_DISABLED_TOOLS to override — an
    | empty string enables everything.
    |
    */

    'disabled_tools' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('MCP_DISABLED_TOOLS', 'delete-contact,delete-note,delete-call,delete-reminder,delete-task'))
    ))),

];
