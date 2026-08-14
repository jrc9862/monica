<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\CreateTask;
use App\Mcp\Tools\DeleteTask;
use App\Mcp\Tools\ListTasks;
use App\Mcp\Tools\ToggleTask;
use App\Models\Contact;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskToolsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_creates_a_task(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);

        $result = (new CreateTask)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Send a card',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertDatabaseHas('contact_tasks', [
            'id' => $data['task']['id'],
            'contact_id' => $contact->id,
            'label' => 'Send a card',
            'completed' => false,
        ]);
    }

    /** @test */
    public function it_lists_tasks(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        (new CreateTask)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Task one',
        ]);

        $result = (new ListTasks)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);
        $this->assertCount(1, $data['tasks']);
        $this->assertSame('Task one', $data['tasks'][0]['label']);
    }

    /** @test */
    public function toggling_a_task_flips_completed(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        $created = (new CreateTask)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Toggle me',
        ]);
        $taskId = json_decode($created->toArray()['content'][0]['text'], true)['task']['id'];

        $result = (new ToggleTask)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'task_id' => $taskId,
        ]);
        $data = json_decode($result->toArray()['content'][0]['text'], true);
        $this->assertTrue($data['task']['completed']);
        $this->assertDatabaseHas('contact_tasks', ['id' => $taskId, 'completed' => true]);

        $result = (new ToggleTask)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'task_id' => $taskId,
        ]);
        $data = json_decode($result->toArray()['content'][0]['text'], true);
        $this->assertFalse($data['task']['completed']);
        $this->assertDatabaseHas('contact_tasks', ['id' => $taskId, 'completed' => false]);
    }

    /** @test */
    public function it_deletes_a_task(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create(['vault_id' => $vault->id]);
        $created = (new CreateTask)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'label' => 'Delete me',
        ]);
        $taskId = json_decode($created->toArray()['content'][0]['text'], true)['task']['id'];

        $result = (new DeleteTask)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
            'task_id' => $taskId,
        ]);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertSoftDeleted('contact_tasks', ['id' => $taskId]);
    }
}
