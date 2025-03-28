<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductOption\DeleteProductOption;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rulesets\ProductOptionGroup\DeleteProductOptionGroupRuleset;
use Illuminate\Validation\ValidationException;

class DeleteProductOptionGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductOptionGroupRuleset::class;
    }

    public function performAction(): array|bool|null
    {
        $productOptionGroup = resolve_static(ProductOptionGroup::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $productOptions = [];
        foreach ($productOptionGroup->productOptions()->pluck('id')->toArray() as $productOption) {
            try {
                DeleteProductOption::make(['id' => $productOption])->validate()->execute();
                $productOptions[$productOption] = true;
            } catch (ValidationException) {
                $productOptions[$productOption] = false;
            }
        }

        return ! array_filter($productOptions, fn ($item) => ! $item) ?
            $productOptionGroup->delete() : ['product_options' => $productOptions];
    }

    protected function validateData(): void
    {
        if (resolve_static(ProductOption::class, 'query')
            ->where('product_option_group_id', $this->getData('id'))
            ->whereHas('products')
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => ['Product with given Product Option exists.'],
            ])
                ->status(423);
        }
    }
}
