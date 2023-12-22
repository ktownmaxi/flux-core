<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateLanguageRequest;
use FluxErp\Models\Language;
use Illuminate\Support\Facades\Validator;

class CreateLanguage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateLanguageRequest())->rules();
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function performAction(): Language
    {
        $this->data['is_default'] = ! Language::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            Language::query()->update(['is_default' => false]);
        }

        $language = new Language($this->data);
        $language->save();

        return $language->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Language());

        $this->data = $validator->validate();
    }
}
