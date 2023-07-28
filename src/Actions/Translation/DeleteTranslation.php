<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\BaseAction;
use Spatie\TranslationLoader\LanguageLine;

class DeleteTranslation extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:language_lines,id',
        ];
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function performAction(): ?bool
    {
        return LanguageLine::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
