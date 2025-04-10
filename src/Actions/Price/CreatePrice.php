<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Price;
use FluxErp\Rulesets\Price\CreatePriceRuleset;

class CreatePrice extends FluxAction
{
    public static function models(): array
    {
        return [Price::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePriceRuleset::class;
    }

    public function performAction(): Price
    {
        $price = app(Price::class, ['attributes' => $this->data]);
        $price->save();

        return $price->fresh();
    }
}
