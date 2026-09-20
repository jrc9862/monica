<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class MonicaServer extends Server
{
    public string $serverName = 'Monica';

    public string $serverVersion = '1.0.0';

    public string $instructions = <<<'EOT'
        Monica is a personal relationship manager. Data is organized in vaults;
        each vault contains contacts, and contacts hold notes, calls, reminders
        and tasks. Most tools take a vault_id — call list-vaults first if you
        do not know it (when the account has a single vault, tools default to
        it). Vault and contact ids are UUIDs; note, task and reminder ids are
        integers. Logging an answered call automatically creates a 90-day
        follow-up reminder.
        EOT;

    /**
     * laravel/mcp paginates tools/list at 15 by default, so the last six tools
     * are only reachable if the client follows the nextCursor. Serve the whole
     * set in one page instead.
     */
    public int $defaultPaginationLength = 30;

    public array $tools = [
        \App\Mcp\Tools\ListVaults::class,
        \App\Mcp\Tools\SearchContacts::class,
        \App\Mcp\Tools\GetContact::class,
        \App\Mcp\Tools\CreateContact::class,
        \App\Mcp\Tools\UpdateContact::class,
        \App\Mcp\Tools\DeleteContact::class,
        \App\Mcp\Tools\ListNotes::class,
        \App\Mcp\Tools\CreateNote::class,
        \App\Mcp\Tools\UpdateNote::class,
        \App\Mcp\Tools\DeleteNote::class,
        \App\Mcp\Tools\LogCall::class,
        \App\Mcp\Tools\ListCalls::class,
        \App\Mcp\Tools\DeleteCall::class,
        \App\Mcp\Tools\ListReminders::class,
        \App\Mcp\Tools\CreateReminder::class,
        \App\Mcp\Tools\UpdateReminder::class,
        \App\Mcp\Tools\DeleteReminder::class,
        \App\Mcp\Tools\ListTasks::class,
        \App\Mcp\Tools\CreateTask::class,
        \App\Mcp\Tools\ToggleTask::class,
        \App\Mcp\Tools\DeleteTask::class,
    ];

    /**
     * Drop tools listed in config('mcp.disabled_tools') before the server
     * serves anything. Both tools/list and tools/call resolve through the
     * registered set, so a disabled tool is neither advertised nor callable.
     */
    public function boot(): void
    {
        $disabled = config('mcp.disabled_tools', []);

        if ($disabled === []) {
            return;
        }

        $this->registeredTools = array_values(array_filter(
            $this->registeredTools,
            fn ($tool) => ! in_array(
                (is_string($tool) ? app($tool) : $tool)->name(),
                $disabled,
                true
            )
        ));
    }

    /**
     * Negotiate the protocol version down instead of failing the handshake.
     *
     * laravel/mcp v0.1.1 rejects any initialize whose protocolVersion is not in
     * $supportedProtocolVersion with a -32602 error. Claude requests 2025-11-25,
     * so the handshake never completes. The MCP spec says a server that does not
     * support the requested version should answer with one it does support and
     * let the client decide, which is what rewriting the request achieves — and
     * unlike adding 2025-11-25 to the supported list, it does not claim
     * conformance to a revision this code has not implemented.
     *
     * Remove once laravel/mcp is upgraded; the current release negotiates properly.
     */
    public function handle(string $rawMessage)
    {
        return parent::handle($this->rewriteRequest($rawMessage));
    }

    /**
     * Apply the request rewrites laravel/mcp v0.1.1 needs, in one pass.
     *
     * A message that is not valid JSON, or that no rewrite touches, is handed
     * on byte for byte so the server produces its own error for it.
     */
    private function rewriteRequest(string $rawMessage): string
    {
        try {
            $payload = \Safe\json_decode($rawMessage, true);
        } catch (\Safe\Exceptions\JsonException) {
            return $rawMessage;
        }

        if (! is_array($payload)) {
            return $rawMessage;
        }

        $rewritten = $this->defaultToolArguments(
            $this->downgradeProtocolVersion($payload)
        );

        return $rewritten === $payload ? $rawMessage : \Safe\json_encode($rewritten);
    }

    /**
     * Rewrite an unsupported initialize protocolVersion to the newest supported
     * one. Anything that is not a well-formed initialize is left alone.
     *
     * @param  array<mixed>  $payload
     * @return array<mixed>
     */
    private function downgradeProtocolVersion(array $payload): array
    {
        if (($payload['method'] ?? null) !== 'initialize') {
            return $payload;
        }

        $requested = $payload['params']['protocolVersion'] ?? null;

        if (! is_string($requested) || in_array($requested, $this->supportedProtocolVersion, true)) {
            return $payload;
        }

        $payload['params']['protocolVersion'] = $this->supportedProtocolVersion[0];

        return $payload;
    }

    /**
     * Give a tools/call an argument list when it arrives without a usable one.
     *
     * `arguments` is optional in the MCP spec — a tool that takes no input is
     * called with `name` alone, which is how list-vaults would be called — but
     * CallTool reads params['arguments'] unguarded. The undefined key escapes
     * Server::handle() as a JSON-RPC protocol error carrying a raw PHP warning
     * ('Undefined array key "arguments"') rather than as a tool result, so the
     * client cannot tell a missing key from a tool that failed.
     *
     * Remove once laravel/mcp guards the read.
     *
     * @param  array<mixed>  $payload
     * @return array<mixed>
     */
    private function defaultToolArguments(array $payload): array
    {
        if (($payload['method'] ?? null) !== 'tools/call') {
            return $payload;
        }

        if (! is_array($payload['params'] ?? null)) {
            return $payload;
        }

        // Also covers a non-array `arguments`, which would type-error against
        // Tool::handle(array $arguments) and surface the same opaque way. The
        // tools report their own missing arguments with a usable message.
        if (! is_array($payload['params']['arguments'] ?? null)) {
            $payload['params']['arguments'] = [];
        }

        return $payload;
    }
}
