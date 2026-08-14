<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateNote;
use App\Models\Contact;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class McpAuthTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function unauthenticated_post_to_mcp_returns_401_with_resource_metadata_header(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'ping',
        ]);

        $response->assertStatus(401);
        $header = $response->headers->get('WWW-Authenticate');
        $this->assertNotNull($header);
        $this->assertStringContainsString('resource_metadata', $header);
    }

    /** @test */
    public function protected_resource_metadata_is_served(): void
    {
        $response = $this->getJson('/.well-known/oauth-protected-resource');

        $response->assertStatus(200);
        $response->assertJsonPath('resource', fn ($resource) => str_ends_with($resource, '/mcp'));
    }

    /** @test */
    public function protected_resource_metadata_suffixed_variant_is_served(): void
    {
        $response = $this->getJson('/.well-known/oauth-protected-resource/mcp');

        $response->assertStatus(200);
        $response->assertJsonPath('resource', fn ($resource) => str_ends_with($resource, '/mcp'));
    }

    /** @test */
    public function authorization_server_metadata_is_served(): void
    {
        $response = $this->getJson('/.well-known/oauth-authorization-server');

        $response->assertStatus(200);
        $response->assertJsonStructure(['registration_endpoint']);
    }

    /** @test */
    public function oauth_register_accepts_the_allowlisted_claude_redirect_uri(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Claude',
            'redirect_uris' => ['https://claude.ai/api/mcp/auth_callback'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['client_id']);
    }

    /** @test */
    public function oauth_register_rejects_a_non_allowlisted_redirect_uri(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Evil',
            'redirect_uris' => ['https://evil.example/cb'],
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function oauth_register_rejects_a_userinfo_spoofed_localhost_redirect_uri(): void
    {
        // "http://localhost:1@evil.com/cb" has host evil.com — the localhost is
        // only userinfo. A prefix match would wrongly accept it.
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Claude',
            'redirect_uris' => ['http://localhost:1@evil.com/cb'],
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function oauth_register_accepts_a_loopback_redirect_uri(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Claude Code',
            'redirect_uris' => ['http://127.0.0.1:49152/callback'],
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function a_valid_sanctum_token_can_call_mcp(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user, ['read', 'write']);

        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'ping',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_real_bearer_token_authenticates_and_completes_the_initialize_handshake(): void
    {
        // Sanctum::actingAs() injects the user directly and never parses a bearer
        // token, so this is the only test covering the header path Claude Code uses.
        // Build the user with the factory rather than createUser(), which would
        // call Sanctum::actingAs() and authenticate the request without the header.
        $user = User::factory()->create();
        $token = $user->createToken('mcp', ['read', 'write'])->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->postJson('/mcp', [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
                'params' => [
                    'protocolVersion' => '2025-06-18',
                    'capabilities' => [],
                    'clientInfo' => ['name' => 'test', 'version' => '1'],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('result.serverInfo.name', 'Monica');
    }

    /** @test */
    public function a_bearer_token_without_its_id_prefix_still_authenticates(): void
    {
        // Monica's Settings → API screen shows the token without the "<id>|"
        // prefix. Sanctum falls back to hashing the whole string when no pipe is
        // present, so the bare token is valid — don't "fix" this into a 401.
        $user = User::factory()->create();
        $token = $user->createToken('mcp', ['read', 'write'])->plainTextToken;
        $withoutPrefix = Str::after($token, '|');

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$withoutPrefix])
            ->postJson('/mcp', [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'ping',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_write_tool_returns_an_error_result_when_the_token_lacks_the_write_ability(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        Sanctum::actingAs($user, ['read']);

        $result = (new CreateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'body' => 'Should be rejected',
        ]);

        $this->assertTrue($result->toArray()['isError']);
        $this->assertDatabaseMissing('notes', ['body' => 'Should be rejected']);
    }
}
