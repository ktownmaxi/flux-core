<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\Cart\DeleteCart;
use FluxErp\Actions\Cart\UpdateCart;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

class CartForm extends FluxForm
{
    #[Locked]
    public ?int $authenticatable_id = null;

    #[Locked]
    public ?string $authenticatable_type = null;

    public ?array $cart_items = null;

    #[Locked]
    public ?int $id = null;

    public ?bool $is_portal_public = null;

    public ?bool $is_public = null;

    public ?string $name = null;

    #[Renderless]
    public function isUserOwned(): bool
    {
        return ! ($this->is_public || $this->is_portal_public)
            || auth()->user()->is(morph_to($this->authenticatable_type, $this->authenticatable_id));
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateCart::class,
            'update' => UpdateCart::class,
            'delete' => DeleteCart::class,
        ];
    }
}
