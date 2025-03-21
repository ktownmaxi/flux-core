<?php

namespace FluxErp\Rulesets\CountryRegion;

use FluxErp\Models\CountryRegion;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCountryRegionRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = CountryRegion::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => CountryRegion::class]),
            ],
        ];
    }
}
