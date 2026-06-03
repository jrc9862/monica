<?php

namespace App\Domains\Settings\ManageUserPreferences\Services;

use App\Interfaces\ServiceInterface;
use App\Models\User;
use App\Services\BaseService;

class StoreUIDensityPreference extends BaseService implements ServiceInterface
{
    private array $data;

    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'author_id' => 'required|uuid|exists:users,id',
            'ui_density' => 'required|string|in:minimal,chunky',
        ];
    }

    public function permissions(): array
    {
        return [
            'author_must_belong_to_account',
        ];
    }

    public function execute(array $data): User
    {
        $this->data = $data;

        $this->validateRules($data);
        $this->updateUser();

        return $this->author;
    }

    private function updateUser(): void
    {
        $this->author->ui_density = $this->data['ui_density'];
        $this->author->save();
    }
}
