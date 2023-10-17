<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormBuilderSection extends Model
{
    use HasTranslations;
    use SoftDeletes;

    public array $translatable = ['name'];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::deleting(function (FormBuilderSection $section) {
            if ($section->isForceDeleting()) {
                $section->fields()->withTrashed()->get()->each(function ($item) {
                    $item->fieldResponses()->withTrashed()->get()->each(function ($item) {
                        $item->forceDelete();
                    });
                    $item->forceDelete();
                });
            } else {
                $section->fields->each(function ($item) {
                    $item->fieldResponses->each(function ($item) {
                        $item->delete();
                    });
                    $item->delete();
                });
            }
        });
    }

    public function fields(): HasMany
    {
        return $this->hasMany(BoltPlugin::getModel('Field'), 'section_id', 'id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(BoltPlugin::getModel('Form'));
    }
}
