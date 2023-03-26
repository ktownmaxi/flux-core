<?php

namespace FluxErp\Http\Requests;

class UpdateCategoryRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:categories,id',
            'parent_id' => 'integer|nullable|exists:categories,id',
            'name' => 'required|string',
            'sort_number' => 'integer|min:0',
        ];
    }
}
