<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use Commentable, Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
        'symbol' => 'string',
        'is_default' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }
}
