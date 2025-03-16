<?php

namespace FluxErp\Rulesets\Printer;

use FluxErp\Rulesets\FluxRuleset;

class UpdatePrinterRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'spooler_name' => 'sometimes|required|string|max:255',
            'location' => 'nullable|string|max:255',
            'make_and_model' => 'nullable|string|max:255',
            'media_sizes' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
