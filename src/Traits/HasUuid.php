<?php

namespace FluxErp\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (! $model->uuid) {
                $model->uuid = Str::uuid()->toString();
            }
        });

        static::saving(function ($model): void {
            $originalUuid = $model->getOriginal('uuid');
            if ($originalUuid !== $model->uuid && ! is_null($originalUuid)) {
                $model->uuid = $originalUuid;
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->mergeCasts([
            'uuid' => 'string',
        ]);
    }
}
