<div x-data="{order: $wire.entangle('order').defer}" x-on:create-order="$openModal(document.getElementById('create'))">
    <x-modal.card id="create" :title="__('New Order')">
        <section>
            <div class="space-y-2.5 divide-y divide-secondary-200">
                <x-select
                    :options="$orderTypes"
                    option-label="name"
                    option-value="id"
                    :label="__('Order type')"
                    wire:model.defer="order.order_type_id"
                />
                <div class="pt-4">
                    <x-select
                        :label="__('Contact')"
                        class="pb-4"
                        wire:model="order.contact_id"
                        option-value="contact_id"
                        option-label="label"
                        option-description="description"
                        :clearable="false"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Address::class),
                            'params' => [
                                'fields' => [
                                    'contact_id',
                                    'firstname',
                                    'lastname',
                                    'company',
                                ],
                                'where' => [
                                    [
                                        'is_main_address',
                                        '=',
                                        true,
                                    ]
                                ],
                                'with' => 'contact.media',
                            ]
                        ]"
                    />
                    <x-select
                        class="pb-4"
                        :label="__('Invoice Address')"
                        wire:model.defer="order.address_invoice_id"
                        option-value="id"
                        option-label="label"
                        option-description="description"
                        :clearable="false"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Address::class),
                            'params' => [
                                'with' => 'contact.media',
                                'where' => [
                                    ['contact_id', '=', $order['contact_id']],
                                ],
                            ]
                        ]"
                    />
                    <x-select
                        :label="__('Delivery Address')"
                        class="pb-4"
                        wire:model.defer="order.address_delivery_id"
                        option-value="id"
                        option-label="label"
                        option-description="description"
                        :clearable="false"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Address::class),
                            'params' => [
                                'with' => 'contact.media',
                                'where' => [
                                    ['contact_id', '=', $order['contact_id']],
                                ],
                            ]
                        ]"
                    />
                </div>
                <div class="space-y-3 pt-4">
                    <x-select
                        :label="__('Client')"
                        :options="$clients"
                        option-value="id"
                        option-label="name"
                        :clearable="false"
                        autocomplete="off"
                        wire:model="order.client_id"
                    />
                    <x-select
                        :label="__('Price list')"
                        :options="$priceLists"
                        option-value="id"
                        option-label="name"
                        :clearable="false"
                        autocomplete="off"
                        wire:model.defer="order.price_list_id"
                        x-bind:disabled="order.is_locked"
                    />
                    <x-select
                        :label="__('Payment method')"
                        :options="$paymentTypes"
                        option-value="id"
                        option-label="name"
                        :clearable="false"
                        autocomplete="off"
                        wire:model.defer="order.payment_type_id"
                        x-bind:disabled="order.is_locked"
                    />
                    <x-select
                        :label="__('Language')"
                        :options="$languages"
                        option-value="id"
                        option-label="name"
                        :clearable="false"
                        autocomplete="off"
                        wire:model.defer="order.language_id"
                        x-bind:disabled="order.is_locked"
                    />
                </div>
            </div>
        </section>
        <x-errors />
        <x-slot name="footer">
            <div class="flex justify-end gap-x-4">
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button spinner primary :label="__('Save')" wire:click="save" />
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <div wire:ignore>
        <livewire:data-tables.order-list :filters="$filters" />
    </div>
</div>
