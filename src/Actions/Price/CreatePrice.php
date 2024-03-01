<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Price;
use FluxErp\Rulesets\Price\CreatePriceRuleset;

class CreatePrice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreatePriceRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function performAction(): Price
    {
        $price = app(Price::class, ['attributes' => $this->data]);
        $price->save();

        return $price->fresh();
    }
}
