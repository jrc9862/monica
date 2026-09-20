<?php

namespace Tests\Unit\Mcp\Tools;

use App\Mcp\Tools\CreateContact;
use App\Mcp\Tools\CreateNote;
use App\Mcp\Tools\DeleteContact;
use App\Mcp\Tools\GetContact;
use App\Mcp\Tools\SearchContacts;
use App\Mcp\Tools\UpdateContact;
use App\Models\Contact;
use App\Models\PassportUser;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Contracts\ScopeAuthorizable;
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
    public function it_creates_a_contact_when_authenticated_through_the_passport_guard(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $this->actAsPassportUser($user);

        $result = (new CreateContact)->handle([
            'vault_id' => $vault->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertSame('Ada Lovelace', $data['name']);
    }

    /** @test */
    public function it_gets_a_contact_when_authenticated_through_the_passport_guard(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'prefix' => null,
            'suffix' => null,
        ]);
        $this->actAsPassportUser($user);

        $result = (new GetContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => $contact->id,
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertSame('Ada Lovelace', $data['name']);
    }

    /** @test */
    public function it_finds_a_contact_by_a_partial_name(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'James',
            'last_name' => 'Collett',
        ]);

        $result = (new SearchContacts)->handle([
            'vault_id' => $vault->id,
            'query' => 'Jam',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertCount(1, $data['contacts']);
    }

    /** @test */
    public function it_finds_a_contact_despite_a_misspelling(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $result = (new SearchContacts)->handle([
            'vault_id' => $vault->id,
            'query' => 'Levelace',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertSame($contact->id, $data['contacts'][0]['id']);
    }

    /** @test */
    public function it_ranks_the_exact_match_first(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        Contact::factory()->create(['vault_id' => $vault->id, 'first_name' => 'Annabel', 'last_name' => 'Lee']);
        $ann = Contact::factory()->create(['vault_id' => $vault->id, 'first_name' => 'Ann', 'last_name' => 'Smith']);
        Contact::factory()->create(['vault_id' => $vault->id, 'first_name' => 'Anneliese', 'last_name' => 'Roth']);

        $result = (new SearchContacts)->handle([
            'vault_id' => $vault->id,
            'query' => 'Ann',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertCount(3, $data['contacts']);
        $this->assertSame($ann->id, $data['contacts'][0]['id']);
    }

    /** @test */
    public function it_matches_a_nickname(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Margaret',
            'nickname' => 'Peggy',
        ]);

        $result = (new SearchContacts)->handle([
            'vault_id' => $vault->id,
            'query' => 'Peggy',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertSame($contact->id, $data['contacts'][0]['id']);
    }

    /** @test */
    public function it_returns_no_results_when_nothing_resembles_the_query(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        Contact::factory()->create(['vault_id' => $vault->id, 'first_name' => 'Ada', 'last_name' => 'Lovelace']);

        $result = (new SearchContacts)->handle([
            'vault_id' => $vault->id,
            'query' => 'Kowalski',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertFalse($result->toArray()['isError']);
        $this->assertSame([], $data['contacts']);
    }

    /** @test */
    public function it_does_not_search_another_vault(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        $other = $this->createVaultUser($user, Vault::PERMISSION_EDIT);
        Contact::factory()->create(['vault_id' => $other->id, 'first_name' => 'Ada', 'last_name' => 'Lovelace']);

        $result = (new SearchContacts)->handle([
            'vault_id' => $vault->id,
            'query' => 'Lovelace',
        ]);

        $data = json_decode($result->toArray()['content'][0]['text'], true);

        $this->assertSame([], $data['contacts']);
    }

    /** @test */
    public function it_reports_a_missing_required_argument(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);

        $result = (new GetContact)->handle(['vault_id' => $vault->id]);

        $this->assertTrue($result->toArray()['isError']);
        $this->assertStringContainsString('contact_id is required', $result->toArray()['content'][0]['text']);
    }

    /** @test */
    public function it_reports_an_argument_of_the_wrong_type(): void
    {
        $user = $this->createUser();
        $vault = $this->createVaultUser($user, Vault::PERMISSION_EDIT);

        $result = (new GetContact)->handle([
            'vault_id' => $vault->id,
            'contact_id' => ['not', 'a', 'string'],
        ]);

        $this->assertTrue($result->toArray()['isError']);
        $this->assertStringContainsString('contact_id must be a string or an integer', $result->toArray()['content'][0]['text']);
    }

    /**
     * Authenticate on the `api` (Passport) guard, the way a real MCP OAuth
     * request does: Auth::user() is then a PassportUser, not a Monica User.
     */
    private function actAsPassportUser(User $user): void
    {
        $token = new class implements ScopeAuthorizable
        {
            public function can(string $scope): bool
            {
                return true;
            }

            public function cant(string $scope): bool
            {
                return ! $this->can($scope);
            }
        };

        $passportUser = PassportUser::findOrFail($user->id)->withAccessToken($token);

        Auth::guard('api')->setUser($passportUser);
        Auth::shouldUse('api');
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
