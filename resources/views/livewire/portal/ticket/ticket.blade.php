<div>
    <div class="pl-8 pr-6 pt-5">
        <h1 class="pt-5 text-3xl font-bold dark:text-gray-50">
            {{ __('Ticket') . ': ' . $ticket['ticket_number'] }}
        </h1>
        <h2 class="pb-8 pt-10 text-base font-bold uppercase dark:text-gray-50">
            {{ __('Information') }}
        </h2>
        <div
            class="md:flex md:space-x-12"
            x-data="{ additionalColumns: @entangle('additionalColumns'), ticket: @entangle('ticket') }"
        >
            <div class="flex-1">
                <div class="space-y-5 dark:text-gray-50">
                    <x-input wire:model="ticket.title" :disabled="true" />
                    <x-textarea
                        wire:model="ticket.description"
                        :disabled="true"
                    />
                    @if (

                        $ticket['model_type'] &&
                        ($widgetComponent = resolve_static(
                            morphed_model($ticket['model_type']),
                            'getLivewireComponentWidget'
                        ))                    )
                        <x-card>
                            <livewire:is
                                :component="$widgetComponent"
                                :modelId="$ticket['model_id']"
                            />
                        </x-card>
                    @endif

                    @section('additional-columns')
                    <template x-for="additionalColumn in additionalColumns">
                        <div>
                            <x-label
                                x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name"
                                x-bind:for="additionalColumn.name"
                            />
                            <x-input
                                x-bind:type="additionalColumn.field_type"
                                x-model="ticket[additionalColumn.name]"
                                :disabled="true"
                            />
                        </div>
                    </template>
                    @show
                    <h2
                        class="pb-8 pt-10 text-base font-bold uppercase dark:text-gray-50"
                    >
                        {{ __('Attachments') }}
                    </h2>
                    <livewire:folder-tree
                        :model-type="\FluxErp\Models\Ticket::class"
                        :model-id="$ticket['id']"
                    />
                </div>
            </div>
        </div>
        <x-flux::tabs wire:model.live="tab" :$tabs>
            <livewire:is
                wire:key="{{ uniqid() }}"
                :component="$tab"
                :model-id="$ticket['id']"
            />
        </x-flux::tabs>
    </div>
</div>
