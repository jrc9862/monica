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
        it). Contact, vault and other ids are UUIDs. Logging an answered call
        automatically creates a 90-day follow-up reminder.
        EOT;

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
        return parent::handle($this->downgradeProtocolVersion($rawMessage));
    }

    /**
     * Rewrite an unsupported initialize protocolVersion to the newest supported
     * one. Any message that is not a well-formed initialize is passed through
     * untouched so the server can produce its own error for it.
     */
    private function downgradeProtocolVersion(string $rawMessage): string
    {
        try {
            $payload = \Safe\json_decode($rawMessage, true);
        } catch (\Safe\Exceptions\JsonException) {
            return $rawMessage;
        }

        if (! is_array($payload) || ($payload['method'] ?? null) !== 'initialize') {
            return $rawMessage;
        }

        $requested = $payload['params']['protocolVersion'] ?? null;

        if (! is_string($requested) || in_array($requested, $this->supportedProtocolVersion, true)) {
            return $rawMessage;
        }

        $payload['params']['protocolVersion'] = $this->supportedProtocolVersion[0];

        return \Safe\json_encode($payload);
    }
}
