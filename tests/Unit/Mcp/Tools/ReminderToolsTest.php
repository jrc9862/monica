<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\CreateReminder;
use App\Mcp\Tools\DeleteReminder;
use App\Mcp\Tools\ListReminders;
use App\Mcp\Tools\UpdateReminder;
use App\Models\Contact;
use App\Models\ContactReminder;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReminderToolsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_creates_a_reminder(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        $result = (new CreateReminder)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Birthday',
            'type' => ContactReminder::TYPE_RECURRING_YEAR,
            'day' => 5,
            'month' => 6,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('contact_reminders', [
            'id' => $data['reminder']['id'],
            'contact_id' => $contact->id,
            'label' => 'Birthday',
            'day' => 5,
            'month' => 6,
        ]);
    }

    /** @test */
    public function it_lists_reminders(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        (new CreateReminder)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Anniversary',
            'type' => ContactReminder::TYPE_ONE_TIME,
            'day' => 1,
            'month' => 1,
            'year' => 2027,
        ]);

        $result = (new ListReminders)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);
        $this->assertCount(1, $data['reminders']);
        $this->assertSame('Anniversary', $data['reminders'][0]['label']);
    }

    /** @test */
    public function it_updates_a_reminder(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        $created = (new CreateReminder)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Old label',
            'type' => ContactReminder::TYPE_ONE_TIME,
            'day' => 1,
            'month' => 1,
            'year' => 2027,
        ]);
        $reminderId = json_decode($created->toArray()['content'][0]['text'], true)['reminder']['id'];

        $result = (new UpdateReminder)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'reminder_id' => $reminderId,
            'label' => 'New label',
            'type' => ContactReminder::TYPE_ONE_TIME,
            'day' => 2,
            'month' => 2,
            'year' => 2027,
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('contact_reminders', [
            'id' => $reminderId,
            'label' => 'New label',
            'day' => 2,
            'month' => 2,
        ]);
    }

    /** @test */
    public function it_deletes_a_reminder(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        $created = (new CreateReminder)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Delete me',
            'type' => ContactReminder::TYPE_ONE_TIME,
            'day' => 1,
            'month' => 1,
            'year' => 2027,
        ]);
        $reminderId = json_decode($created->toArray()['content'][0]['text'], true)['reminder']['id'];

        $result = (new DeleteReminder)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'reminder_id' => $reminderId,
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseMissing('contact_reminders', ['id' => $reminderId]);
    }
}
