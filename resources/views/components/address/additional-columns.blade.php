<div x-data="{edit: $wire.entangle('edit')}">
    <x-additional-columns
        wire="address.additional_columns"
        :model="\FluxErp\Models\Address::class"
        :model-id="$this->address->id"
        table
    />
</div>
