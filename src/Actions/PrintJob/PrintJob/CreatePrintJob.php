<?php

namespace FluxErp\Actions\PrintJob\PrintJob;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PrintJob;
use FluxErp\Rulesets\PrintJob\CreatePrintJobRuleset;

class CreatePrintJob extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreatePrintJobRuleset::class;
    }

    public static function models(): array
    {
        return [PrintJob::class];
    }

    public function performAction(): PrintJob
    {
        $printJob = app(PrintJob::class, ['attributes' => $this->getData()]);
        $printJob->save();

        return $printJob->fresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['user_id'] ??= auth()->id();
        $this->data['quantity'] ??= 1;
    }
}
