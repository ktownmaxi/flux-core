<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rulesets\Role\UpdateRoleUsersRuleset;

class UpdateRoleUsers extends FluxAction
{
    public static function models(): array
    {
        return [Role::class, User::class];
    }

    public static function name(): string
    {
        return 'role.update-users';
    }

    protected function getRulesets(): string|array
    {
        return UpdateRoleUsersRuleset::class;
    }

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->setData($this->data ? array_merge(['assign' => true], $this->data) : []);
    }

    public function performAction(): array
    {
        $role = resolve_static(Role::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($this->data['assign']) {
            $role->users()->syncWithoutDetaching($this->data['users']);
        } else {
            $role->users()->detach($this->data['users']);
        }

        return $role->users->toArray();
    }
}
