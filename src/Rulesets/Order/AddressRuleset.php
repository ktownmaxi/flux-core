<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class AddressRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'addresses' => 'array',
            'addresses.*.address_id' => [
                'required',
                'integer',
                new ModelExists(Address::class),
            ],
            'addresses.*.address_type_id' => [
                'required',
                'integer',
                new ModelExists(AddressType::class),
            ],
        ];
    }
}
