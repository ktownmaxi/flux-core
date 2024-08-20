<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateAddressRuleset extends FluxRuleset
{
    protected static ?string $model = Address::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:addresses,uuid',
            'client_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'contact_id' => [
                'required',
                'integer',
                app(ExistsWithForeign::class, ['foreignAttribute' => 'client_id', 'table' => 'contacts']),
            ],
            'country_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'language_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'date_of_birth' => 'date|nullable',
            'department' => 'string|nullable',
            'email' => [
                'nullable',
                'email',
                Rule::unique('addresses', 'email')
                    ->whereNull('deleted_at'),
            ],
            'password' => 'string|nullable',
            'is_main_address' => 'boolean',
            'is_invoice_address' => 'boolean',
            'is_delivery_address' => 'boolean',
            'is_active' => 'boolean',
            'can_login' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(PostalAddressRuleset::class, 'getRules'),
            resolve_static(AddressTypeRuleset::class, 'getRules'),
            resolve_static(ContactOptionRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules'),
            resolve_static(PermissionRuleset::class, 'getRules'),
            [
                'contact_options.*.id' => 'exclude',
            ]
        );
    }
}
