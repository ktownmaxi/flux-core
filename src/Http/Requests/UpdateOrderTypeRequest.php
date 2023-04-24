<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Rules\ExistsWithIgnore;
use Illuminate\Validation\Rules\Enum;

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
            'print_layouts.*' => 'required|string',
            'order_type_enum' => [
                'required',
                new Enum(OrderTypeEnum::class),
            ],
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }
}
