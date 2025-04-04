<?php

namespace FluxErp\Actions\ProductPropertyGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductProperty\CreateProductProperty;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rulesets\ProductPropertyGroup\CreateProductPropertyGroupRuleset;
use Illuminate\Support\Arr;

class CreateProductPropertyGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductPropertyGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductPropertyGroupRuleset::class;
    }

    public function performAction(): ProductPropertyGroup
    {
        $productProperties = Arr::pull($this->data, 'product_properties', []);

        $productPropertyGroup = app(ProductPropertyGroup::class, ['attributes' => $this->data]);
        $productPropertyGroup->save();

        foreach ($productProperties as $productProperty) {
            $productProperty = array_merge(
                $productProperty,
                ['product_property_group_id' => $productPropertyGroup->id]
            );
            CreateProductProperty::make($productProperty)
                ->validate()
                ->execute();
        }

        return $productPropertyGroup->fresh();
    }
}
