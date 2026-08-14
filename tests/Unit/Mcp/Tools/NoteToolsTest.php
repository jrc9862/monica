<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\CreateNote;
use App\Mcp\Tools\DeleteNote;
use App\Mcp\Tools\ListNotes;
use App\Mcp\Tools\UpdateNote;
use App\Models\Contact;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NoteToolsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_creates_a_note(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        $result = (new CreateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'title' => 'Title',
            'body' => 'Body',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('notes', [
            'id' => $data['id'],
            'contact_id' => $contact->id,
            'title' => 'Title',
            'body' => 'Body',
        ]);
    }

    /** @test */
    public function it_lists_notes(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        (new CreateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'body' => 'Body one',
        ]);

        $result = (new ListNotes)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);
        $this->assertCount(1, $data['notes']);
        $this->assertSame('Body one', $data['notes'][0]['body']);
    }

    /** @test */
    public function it_updates_a_note(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        $created = (new CreateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'body' => 'Original',
        ]);
        $noteId = json_decode($created->toArray()['content'][0]['text'], true)['id'];

        $result = (new UpdateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'note_id' => $noteId,
            'body' => 'Updated',
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('notes', ['id' => $noteId, 'body' => 'Updated']);
    }

    /** @test */
    public function it_deletes_a_note(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        $created = (new CreateNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'body' => 'Bye',
        ]);
        $noteId = json_decode($created->toArray()['content'][0]['text'], true)['id'];

        $result = (new DeleteNote)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'note_id' => $noteId,
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseMissing('notes', ['id' => $noteId]);
    }
}
