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
}
