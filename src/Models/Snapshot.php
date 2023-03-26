<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid;

    protected $hidden = [
        'uuid',
        'model_type',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];
}
