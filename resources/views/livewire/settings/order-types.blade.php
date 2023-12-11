<div class="py-6" x-data="{orderType: @entangle('orderType')}">
    <div class="px-4 sm:px-6 lg:px-8" wire:ignore>
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Order Types') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('A list of all the order types') }}</div>
            </div>
        </div>
        <div x-on:data-table-row-clicked="$wire.showEditModal($event.detail.id)">
            <livewire:data-tables.order-type-list />
        </div>
    </div>

    <x-modal.card :title="$orderType['id'] ? __('Edit Order Type') : __('New Order Type')" wire:model="editModal">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model="orderType.name" :label="__('Order Type Name')"/>
                            <x-textarea wire:model="orderType.description" :label="__('Description')"/>
                            <x-select label="{{ __('Client') }}"
                                      placeholder="{{ __('Select a Client') }}"
                                      wire:model="orderType.client_id"
                                      :options="$clients"
                                      option-label="name"
                                      option-value="id"
                            />
                            <x-select label="{{ __('Order Type') }}"
                                      :disabled="(bool) $orderType['id']"
                                      placeholder="{{ __('Select Order Type') }}"
                                      wire:model="orderType.order_type_enum"
                                      :options="$enum"
                            />
                            <x-select label="{{ __('Print Layouts') }}"
                                      placeholder="{{ __('Select a Print Layout') }}"
                                      wire:model="orderType.print_layouts"
                                      multiselect
                                      option-label="label"
                                      option-value="value"
                                      :options="$printViews"
                            />

                            <x-checkbox wire:model="orderType.is_active" :label="__('Is Active')"/>
                            <x-checkbox wire:model="orderType.is_hidden" :label="__('Is Hidden')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <div x-bind:class="orderType['id'] > 0 || 'invisible'">
                    <x-button flat negative :label="__('Delete')" x-on:click="close" wire:click="delete"/>
                </div>
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="save"/>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
</div>
