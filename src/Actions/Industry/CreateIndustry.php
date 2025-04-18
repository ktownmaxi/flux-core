<?php

namespace FluxErp\Actions\Industry;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Industry;
use FluxErp\Rulesets\Industry\CreateIndustryRuleset;

class CreateIndustry extends FluxAction
{
    public static function models(): array
    {
        return [Industry::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateIndustryRuleset::class;
    }

    public function performAction(): Industry
    {
        $industry = app(Industry::class, ['attributes' => $this->getData()]);
        $industry->save();

        return $industry->fresh();
    }
}
