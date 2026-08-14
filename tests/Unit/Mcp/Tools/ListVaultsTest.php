<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\ListVaults;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ListVaultsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_lists_the_users_vaults(): void
    {
        $user = $this->createUser();
        $vaultOne = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $vaultTwo = $this->createVault($user->account);
        $this->setPermissionInVault($user, Vault::PERMISSION_VIEW, $vaultTwo);

        $result = (new ListVaults)->handle([]);
        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $ids = collect($data['vaults'])->pluck('id')->all();
        $this->assertContains($vaultOne->id, $ids);
        $this->assertContains($vaultTwo->id, $ids);
        $this->assertCount(2, $data['vaults']);
    }

    /** @test */
    public function it_excludes_vaults_the_user_is_not_in(): void
    {
        $user = $this->createUser();
        $this->createVaultUser($user, Vault::PERMISSION_EDIT);

        $otherAccount = $this->createAccount();
        $otherVault = $this->createVault($otherAccount);

        $result = (new ListVaults)->handle([]);
        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $ids = collect($data['vaults'])->pluck('id')->all();
        $this->assertNotContains($otherVault->id, $ids);
        $this->assertCount(1, $data['vaults']);
    }
}
