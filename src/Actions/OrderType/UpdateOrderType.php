<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\UpdateOrderTypeRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateOrderType extends FluxAction
{
    public static function models(): array
    {
        return [OrderType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateOrderTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $orderType = resolve_static(OrderType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $orderType->fill($this->data);
        $orderType->save();

        return $orderType->withoutRelations()->fresh();
    }
}
