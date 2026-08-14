<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithMonica;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolResult;

#[IsReadOnly]
class ListVaults extends Tool
{
    use InteractsWithMonica;

    public function description(): string
    {
        return 'List the vaults the user has access to. Vaults are the top-level containers holding contacts.';
    }

    public function handle(array $arguments): ToolResult
    {
        return $this->guard(function () {
            $this->ensureTokenCan('read');

            $vaults = $this->author()->vaults()->get()
                ->map(fn ($vault) => $this->vaultSummary($vault))
                ->values()
                ->all();

            return ToolResult::json(['vaults' => $vaults]);
        });
    }
}
