<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\CreateNote;
use App\Mcp\Tools\ListNotes;
use App\Models\Contact;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VaultScopingTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_user_not_in_the_vault_cannot_list_notes(): void
    {
        $owner = $this->createUser();
        $vault = $this->createVaultUser($owner, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        // switch active auth to a user with no access to $vault
        $outsider = $this->createUser();

        $result = (new ListNotes)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $this->assertTrue($result->toArray()['isError']);
    }

    /** @test */
    public function a_user_not_in_the_vault_cannot_create_a_note(): void
    {
        $owner = $this->createUser();
        $vault = $this->createVaultUser($owner, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        $outsider = $this->createUser();

        $result = (new CreateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'body' => 'Should not be created',
        ]);

        $this->assertTrue($result->toArray()['isError']);
        $this->assertDatabaseMissing('notes', ['body' => 'Should not be created']);
    }
}
