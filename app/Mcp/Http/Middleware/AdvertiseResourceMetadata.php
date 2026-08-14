<?php

namespace App\Mcp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MCP clients discover the OAuth authorization server through the
 * WWW-Authenticate header of a 401 response (RFC 9728); Laravel's auth
 * middleware does not emit it on its own.
 */
class AdvertiseResourceMetadata
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_UNAUTHORIZED) {
            $response->headers->set(
                'WWW-Authenticate',
                'Bearer resource_metadata="'.url('/.well-known/oauth-protected-resource/mcp').'"'
            );
        }

        return $response;
    }
}
