<?php

namespace FluxErp\Models;

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAttributeTranslations;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductProperty extends FluxModel
{
    use Filterable, HasAttributeTranslations, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected $hidden = [
        'pivot',
    ];

    protected function casts(): array
    {
        return [
            'property_type_enum' => PropertyTypeEnum::class,
        ];
    }

    public function productPropertyGroup(): BelongsTo
    {
        return $this->belongsTo(ProductPropertyGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_product_property',
            'product_prop_id',
            'product_id'
        );
    }

    protected function translatableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
