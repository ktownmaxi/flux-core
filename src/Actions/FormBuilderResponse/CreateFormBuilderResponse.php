<?php

namespace FluxErp\Actions\FormBuilderResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rulesets\FormBuilderResponse\CreateFormBuilderResponseRuleset;

class CreateFormBuilderResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateFormBuilderResponseRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderResponse::class];
    }

    public function performAction(): FormBuilderResponse
    {
        $formBuilderResponse = app(FormBuilderResponse::class, ['attributes' => $this->data]);
        $formBuilderResponse->save();

        return $formBuilderResponse->refresh();
    }
}
