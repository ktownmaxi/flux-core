<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateOrderTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:order_types,id,deleted_at,NULL',
            'client_id' => [
                'integer',
                (new ExistsWithIgnore('clients', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'string',
            'description' => 'string|nullable',
            'print_layouts' => 'array|nullable',
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }
}
