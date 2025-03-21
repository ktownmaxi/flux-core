<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SepaMandate;
use FluxErp\Rulesets\SepaMandate\DeleteSepaMandateRuleset;

class DeleteSepaMandate extends FluxAction
{
    public static function models(): array
    {
        return [SepaMandate::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteSepaMandateRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(SepaMandate::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
