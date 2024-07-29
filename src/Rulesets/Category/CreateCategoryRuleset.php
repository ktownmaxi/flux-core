<?php

namespace FluxErp\Rulesets\Category;

use FluxErp\Models\Category;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCategoryRuleset extends FluxRuleset
{
    protected static ?string $model = Category::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:categories,uuid',
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class),
            ],
            'parent_id' => [
                'integer',
                app(ModelExists::class, ['model' => Category::class]),
            ],
            'name' => 'required|string',
            'sort_number' => 'required|integer',
            'is_active' => 'boolean',
        ];
    }
}
