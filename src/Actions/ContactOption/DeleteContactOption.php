<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOption;
use FluxErp\Rulesets\ContactOption\DeleteContactOptionRuleset;

class DeleteContactOption extends FluxAction
{
    public static function models(): array
    {
        return [ContactOption::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteContactOptionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(ContactOption::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
