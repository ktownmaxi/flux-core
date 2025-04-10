<?php

namespace FluxErp\Actions\ProductPropertyGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductProperty\DeleteProductProperty;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rulesets\ProductPropertyGroup\DeleteProductPropertyGroupRuleset;
use Illuminate\Validation\ValidationException;

class DeleteProductPropertyGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductPropertyGroupRuleset::class;
    }

    public function performAction(): array|bool|null
    {
        $productPropertyGroup = resolve_static(ProductPropertyGroup::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $productProperties = [];
        foreach ($productPropertyGroup->productProperties()->pluck('id')->toArray() as $productProperty) {
            try {
                DeleteProductProperty::make(['id' => $productProperty])->validate()->execute();
                $productProperties[$productProperty] = true;
            } catch (ValidationException) {
                $productProperties[$productProperty] = false;
            }
        }

        return ! array_filter($productProperties, fn ($item) => ! $item) ?
            $productPropertyGroup->delete() : ['product_options' => $productProperties];
    }
}
