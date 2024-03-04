<?php

namespace FluxErp\Http\Requests;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Printable;

class PrintingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(uses: Printable::class, implements: OffersPrinting::class),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
            'view' => 'required|string',
            'html' => 'exclude_if:preview,true|boolean',
            'preview' => 'boolean',
        ];
    }
}
