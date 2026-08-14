<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\ClientRepository;

use function Safe\parse_url;

/*
|--------------------------------------------------------------------------
| MCP OAuth discovery + dynamic client registration
|--------------------------------------------------------------------------
| Registered at the root (not under the /mcp prefix) because RFC 8414 /
| RFC 9728 clients resolve .well-known documents from the origin root.
| Passport's own /oauth/authorize and /oauth/token routes handle the
| actual authorization-code flow.
*/

$protectedResource = fn () => response()->json([
    'resource' => url('/mcp'),
    'authorization_servers' => [config('app.url')],
    'scopes_supported' => ['read', 'write'],
    'bearer_methods_supported' => ['header'],
]);

$authorizationServer = fn () => response()->json([
    'issuer' => config('app.url'),
    'authorization_endpoint' => url('/oauth/authorize'),
    'token_endpoint' => url('/oauth/token'),
    'registration_endpoint' => url('/oauth/register'),
    'scopes_supported' => ['read', 'write'],
    'response_types_supported' => ['code'],
    'grant_types_supported' => ['authorization_code', 'refresh_token'],
    'code_challenge_methods_supported' => ['S256'],
    'token_endpoint_auth_methods_supported' => ['none'],
]);

Route::middleware('throttle:mcp')->group(function () use ($protectedResource, $authorizationServer) {
    Route::get('/.well-known/oauth-protected-resource', $protectedResource);
    Route::get('/.well-known/oauth-protected-resource/{path}', $protectedResource)->where('path', '.*');
    Route::get('/.well-known/oauth-authorization-server', $authorizationServer);
    Route::get('/.well-known/oauth-authorization-server/{path}', $authorizationServer)->where('path', '.*');

    Route::post('/oauth/register', function (Request $request) {
        $payload = $request->json()->all();

        $name = $payload['client_name'] ?? null;
        $redirectUris = $payload['redirect_uris'] ?? [];

        // Validate by parsing the URI, not prefix-matching the string: a prefix
        // check treats "http://localhost:1@evil.com/cb" as a localhost URI
        // because everything before "@" is userinfo, not the host.
        $allowed = function ($uri): bool {
            if (! is_string($uri)) {
                return false;
            }
            if ($uri === 'https://claude.ai/api/mcp/auth_callback') {
                return true;
            }
            try {
                $parts = parse_url($uri);
            } catch (\Safe\Exceptions\UrlException) {
                return false;
            }

            return ($parts['scheme'] ?? '') === 'http'
                && in_array($parts['host'] ?? '', ['localhost', '127.0.0.1'], true)
                && ! isset($parts['user'], $parts['pass']);
        };

        if (! is_string($name) || $name === '' || ! is_array($redirectUris) || $redirectUris === []
            || collect($redirectUris)->contains(fn ($uri) => ! $allowed($uri))) {
            return response()->json([
                'error' => 'invalid_client_metadata',
                'error_description' => 'client_name and an allow-listed redirect_uris array are required.',
            ], 400);
        }

        // Reuse an existing client with the same name + redirect set instead of
        // inserting a duplicate, and cap total dynamic clients, so an
        // unauthenticated caller cannot flood oauth_clients.
        $existing = \Laravel\Passport\Client::query()
            ->where('name', $name)
            ->get()
            ->first(fn ($c) => $c->redirect_uris === $redirectUris);

        if ($existing !== null) {
            return response()->json([
                'client_id' => $existing->id,
                'client_name' => $existing->name,
                'redirect_uris' => $existing->redirect_uris,
                'token_endpoint_auth_method' => 'none',
                'grant_types' => ['authorization_code', 'refresh_token'],
                'response_types' => ['code'],
            ], 201);
        }

        if (\Laravel\Passport\Client::query()->count() >= 50) {
            return response()->json([
                'error' => 'access_denied',
                'error_description' => 'The dynamic client registration limit has been reached.',
            ], 403);
        }

        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            name: $name,
            redirectUris: $redirectUris,
            confidential: false,
            user: null,
        );

        return response()->json([
            'client_id' => $client->id,
            'client_name' => $client->name,
            'redirect_uris' => $client->redirect_uris,
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
        ], 201);
    });
});
