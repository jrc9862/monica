<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\CreateContact;
use App\Mcp\Tools\CreateNote;
use App\Mcp\Tools\DeleteContact;
use App\Mcp\Tools\GetContact;
use App\Mcp\Tools\SearchContacts;
use App\Mcp\Tools\UpdateContact;
use App\Models\Contact;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ContactToolsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_creates_a_contact(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);

        $result = (new CreateContact)->handle([
            'vault_id' => $vault->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('contacts', [
            'id' => $data['id'],
            'vault_id' => $vault->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
    }

    /** @test */
    public function it_gets_a_contact_with_name_and_notes(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        (new CreateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'title' => 'A note title',
            'body' => 'A note body',
        ]);

        $result = (new GetContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertSame($contact->name, $data['name']);
        $this->assertCount(1, $data['notes']);
        $this->assertSame('A note title', $data['notes'][0]['title']);
    }

    /** @test */
    public function it_updates_a_contact(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id, 'first_name' => 'Old']);

        $result = (new UpdateContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'first_name' => 'New',
            'last_name' => 'Name',
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'New',
            'last_name' => 'Name',
        ]);
    }

    /** @test */
    public function it_deletes_a_contact(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id, 'can_be_deleted' => true]);

        $result = (new DeleteContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function it_refuses_to_delete_a_contact_that_cant_be_deleted(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id, 'can_be_deleted' => false]);

        $result = (new DeleteContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $this->assertTrue($result->toArray()['isError']);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'deleted_at' => null]);
    }

    /** @test */
    public function it_finds_a_contact_by_name_via_search(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Zaphod',
            'last_name' => 'Beeblebrox',
            'listed' => true,
        ]);

        $result = (new SearchContacts)->handle([
            'vault_id' => $vault->id,
            'query' => 'Zaphod',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $ids = collect($data['contacts'])->pluck('id')->all();
        $this->assertContains($contact->id, $ids);
    }

    /** @test */
    public function a_user_from_a_different_account_gets_an_error_not_an_exception(): void
    {
        $owner = $this->createUser();
        $vault = $this->createVaultUser($owner, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        $outsider = $this->createUser();

        $result = (new GetContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $this->assertTrue($result->toArray()['isError']);
    }

    /** @test */
    public function a_contact_from_another_vault_yields_not_found(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);

        $otherVault = $this->createVault($user->account);
        $otherContact = Contact::factory()->create(['vault_id' => $otherVault->id]);

        $result = (new GetContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $otherContact->id,
        ]);

        $this->assertTrue($result->toArray()['isError']);
        $this->assertStringContainsString('not found', strtolower($result->toArray()['content'][0]['text']));
    }
}
