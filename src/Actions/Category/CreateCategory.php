<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Category;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\Category\CreateCategoryRuleset;
use Illuminate\Support\Facades\Validator;

class CreateCategory extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCategoryRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Category::class];
    }

    public function performAction(): Category
    {
        $category = app(Category::class, ['attributes' => $this->data]);
        $category->save();

        return $category->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['sort_number'] = $this->data['sort_number'] ?? 0;
        $this->rules = array_merge(
            $this->rules,
            [
                'parent_id' => [
                    'integer',
                    'nullable',
                    (new ModelExists(Category::class))
                        ->where('model_type', $this->data['model_type'] ?? null),
                ],
            ]
        );
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Category::class));

        $this->data = $validator->validate();
    }
}
