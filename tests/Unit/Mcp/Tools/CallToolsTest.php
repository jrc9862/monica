<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\DeleteCall;
use App\Mcp\Tools\ListCalls;
use App\Mcp\Tools\LogCall;
use App\Models\Contact;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CallToolsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function logging_an_answered_call_creates_the_call_and_a_90_day_follow_up_reminder(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        $result = (new LogCall)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'called_at' => '2026-01-01',
            'type' => 'audio',
            'who_initiated' => 'me',
            'answered' => true,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('calls', [
            'id' => $data['call']['id'],
            'contact_id' => $contact->id,
            'answered' => true,
        ]);
        $this->assertDatabaseHas('contact_reminders', [
            'contact_id' => $contact->id,
            'day' => 1,
            'month' => 4,
            'year' => 2026,
        ]);
    }

    /** @test */
    public function logging_an_unanswered_call_does_not_create_a_follow_up_reminder(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        (new LogCall)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'called_at' => '2026-01-01',
            'type' => 'audio',
            'who_initiated' => 'me',
            'answered' => false,
        ]);

        $this->assertDatabaseMissing('contact_reminders', [
            'contact_id' => $contact->id,
        ]);
    }

    /** @test */
    public function it_lists_calls(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        (new LogCall)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'called_at' => '2026-01-01',
            'type' => 'audio',
            'who_initiated' => 'me',
        ]);

        $result = (new ListCalls)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);
        $this->assertCount(1, $data['calls']);
    }

    /** @test */
    public function it_deletes_a_call(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        $created = (new LogCall)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'called_at' => '2026-01-01',
            'type' => 'audio',
            'who_initiated' => 'me',
        ]);
        $callId = json_decode($created->toArray()['content'][0]['text'], true)['call']['id'];

        $result = (new DeleteCall)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'call_id' => $callId,
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseMissing('calls', ['id' => $callId]);
    }
}
