<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Categorizable extends MorphPivot
{
    use BroadcastsEvents;

    protected $table = 'categorizables';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categorizable(): MorphTo
    {
        return $this->morphTo();
    }
}
