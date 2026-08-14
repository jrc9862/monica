<?php

namespace App\Mcp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Diagnostic logging for the MCP endpoint, off unless MCP_DEBUG_PAYLOADS=true.
 *
 * laravel/mcp catches any Throwable in Server::handle() and returns it as a
 * JSON-RPC error with HTTP 200, without reporting it — so a failing request
 * looks identical to a successful one in the access log. This dumps the raw
 * request and response bodies so those failures are readable.
 *
 * Bodies can contain vault data, so this stays disabled by default.
 */
class LogMcpPayloads
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('mcp.debug_payloads')) {
            return $next($request);
        }

        Log::debug('MCP request', [
            'body' => $request->getContent(),
            'accept' => $request->header('Accept'),
            'content_type' => $request->header('Content-Type'),
            'session' => $request->header('Mcp-Session-Id'),
            'user_agent' => $request->userAgent(),
        ]);

        $response = $next($request);

        Log::debug('MCP response', [
            'status' => $response->getStatusCode(),
            'content_type' => $response->headers->get('Content-Type'),
            'body' => method_exists($response, 'getContent') ? $response->getContent() : '(streamed)',
        ]);

        return $response;
    }
}
