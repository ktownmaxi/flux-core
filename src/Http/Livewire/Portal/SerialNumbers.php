<?php

namespace FluxErp\Http\Livewire\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\SerialNumber;
use FluxErp\Traits\Livewire\WithAddressAuth;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SerialNumbers extends Component
{
    use WithAddressAuth;

    public string $search = '';

    public array $addresses;

    // TODO: refactor to array
    public $serialNumbers;

    protected function getListeners(): array
    {
        $addresses = Address::query()
            ->where('contact_id', Auth::user()->contact_id)
            ->get();

        $listeners = [];
        foreach ($addresses as $address) {
            $channel = $address->broadcastChannel(false);
            $listeners = array_merge($listeners, [
                'echo-private:' . $channel . ',.SerialNumberCreated' => 'serialNumberCreatedEvent',
                'echo-private:' . $channel . ',.SerialNumberUpdated' => 'serialNumberUpdatedEvent',
                'echo-private:' . $channel . ',.SerialNumberDeleted' => 'serialNumberDeletedEvent',
            ]);
        }

        return $listeners;
    }

    public function boot(): void
    {
        $this->addresses = Auth::user()
            ->contact
            ->addresses
            ->pluck('id')
            ->toArray();

        $this->updatedSearch();
    }

    public function render(): mixed
    {
        return view('flux::livewire.portal.serial-numbers')
            ->layout('flux::components.layouts.portal');
    }

    public function updatedSearch(): void
    {
        $this->serialNumbers = SerialNumber::search($this->search)
            ->whereIn('address_id', $this->addresses)
            ->get()
            ->load('product');
    }

    public function serialNumberCreatedEvent(array $data): void
    {
    }

    public function serialNumberUpdatedEvent(array $data): void
    {
    }

    public function serialNumberDeletedEvent(array $data): void
    {
    }
}
