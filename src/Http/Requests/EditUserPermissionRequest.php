<?php

namespace FluxErp\Http\Requests;

class EditUserPermissionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'sync' => 'sometimes|required|boolean',
            'give' => 'sometimes|required|boolean',
            'permissions' => 'required_without:sync|array',
            'permissions.*' => 'required|integer|exists:permissions,id',
        ];
    }
}
