<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Unit;

class DeleteUnit extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:units,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function performAction(): ?bool
    {
        return Unit::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
