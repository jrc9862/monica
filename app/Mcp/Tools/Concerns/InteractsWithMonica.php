<?php

namespace App\Mcp\Tools\Concerns;

use App\Exceptions\CantBeDeletedException;
use App\Exceptions\NotEnoughPermissionException;
use App\Models\PassportUser;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Server\Tools\ToolResult;

trait InteractsWithMonica
{
    /**
     * The Monica user behind the authenticated token (Sanctum PAT or
     * Passport OAuth token).
     */
    protected function author(): User
    {
        $user = auth()->user();

        if ($user instanceof PassportUser) {
            return $user->monicaUser();
        }

        /** @var User $user */
        return $user;
    }

    /**
     * account_id + author_id keys every BaseService expects.
     */
    protected function baseData(User $author): array
    {
        return [
            'account_id' => $author->account_id,
            'author_id' => $author->id,
        ];
    }

    /**
     * Resolve the vault to operate on: an explicit vault_id argument, or the
     * user's only vault when the argument is omitted.
     */
    protected function resolveVaultId(User $author, array $arguments): string
    {
        if (isset($arguments['vault_id']) && $arguments['vault_id'] !== '') {
            if (! is_string($arguments['vault_id'])) {
                throw ValidationException::withMessages([
                    'vault_id' => 'vault_id must be a string (a vault UUID).',
                ]);
            }

            return $arguments['vault_id'];
        }

        $vaults = $author->vaults()->pluck('vaults.id');

        if ($vaults->count() === 1) {
            return $vaults->first();
        }

        throw ValidationException::withMessages([
            'vault_id' => 'vault_id is required because the user has access to '.$vaults->count().' vaults. Use the list-vaults tool.',
        ]);
    }

    /**
     * Resolve a vault the author actually belongs to, in a single query. Read
     * tools use this to scope their Eloquent queries; it fails closed with a
     * ModelNotFoundException when the author is not a member of the vault.
     */
    protected function authorVault(User $author, array $arguments): Vault
    {
        $vaultId = $this->resolveVaultId($author, $arguments);

        /** @var Vault */
        return $author->vaults()->where('vaults.id', $vaultId)->firstOrFail();
    }

    /**
     * Ensure the current token carries the given ability/scope
     * ('read' or 'write'). Web sessions (TransientToken) always pass.
     */
    protected function ensureTokenCan(string $ability): void
    {
        $user = auth()->user();

        if ($user !== null && method_exists($user, 'tokenCan') && ! $user->tokenCan($ability)) {
            throw new NotEnoughPermissionException("The token is missing the '$ability' scope.");
        }
    }

    /**
     * Run a tool body, converting Monica's domain exceptions into MCP error
     * results instead of 500s.
     */
    protected function guard(callable $callback): ToolResult
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            return ToolResult::error('Validation failed: '.collect($e->errors())->flatten()->implode(' '));
        } catch (NotEnoughPermissionException $e) {
            return ToolResult::error('Permission denied: '.($e->getMessage() ?: 'the user cannot perform this action.'));
        } catch (ModelNotFoundException) {
            return ToolResult::error('Record not found: check the id and vault_id.');
        } catch (CantBeDeletedException $e) {
            return ToolResult::error('This record cannot be deleted: '.($e->getMessage() ?: 'it is protected.'));
        } catch (\Throwable $e) {
            // Bad argument types (missing required keys, an array where a
            // string is expected) surface here; return a clean tool error
            // instead of a 500, and keep the detail server-side.
            report($e);

            return ToolResult::error('The request could not be completed. Check the tool arguments and try again.');
        }
    }

    /**
     * Compact array representation of a vault.
     */
    protected function vaultSummary(Vault $vault): array
    {
        return [
            'id' => $vault->id,
            'name' => $vault->name,
            'description' => $vault->description,
        ];
    }
}
