<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Authenticatable used exclusively by the `api` (Passport) guard for MCP
 * OAuth clients. It maps to the same `users` table as User, but exists as a
 * separate class because Passport's HasApiTokens trait method signatures are
 * incompatible with the Sanctum trait already used on User (via Jetstream).
 */
class PassportUser extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens;

    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Get the underlying Monica user for this authenticatable.
     */
    public function monicaUser(): User
    {
        return User::findOrFail($this->getKey());
    }
}
