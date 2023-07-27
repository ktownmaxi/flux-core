<div>
    <div class="pt-5 pl-8 pr-6">
        <h1 class="pt-5 text-3xl font-bold dark:text-gray-50">
            {{ __('Ticket') . ': ' . $ticket['ticket_number'] }}
        </h1>
        <h2 class="pt-10 pb-8 text-base font-bold uppercase dark:text-gray-50">{{ __('Information') }}</h2>
        <div class="md:flex md:space-x-12" x-data="{additionalColumns: @entangle('additionalColumns').defer, ticket: @entangle('ticket')}" >
            <div class="flex-1">
                <div class="space-y-5 dark:text-gray-50">
                    <x-input wire:model="ticket.title" :disabled="true"/>
                    <x-textarea wire:model="ticket.description" :disabled="true"/>
                    @if($ticket['model_type'] && $ticket['model_type']::getLivewireComponentWidget())
                        <x-card>
                            <livewire:is :component="$ticket['model_type']::getLivewireComponentWidget()" :modelId="$ticket['model_id']" />
                        </x-card>
                    @endif
                    <template x-for="additionalColumn in additionalColumns">
                        <div>
                            <x-label
                                x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name"
                                x-bind:for="additionalColumn.name"
                            />
                            <x-input x-bind:type="additionalColumn.field_type" x-model="ticket[additionalColumn.name]" :disabled="true"/>
                        </div>
                    </template>
                    <h2 class="pt-10 pb-8 text-base font-bold uppercase dark:text-gray-50">{{ __('Attachments') }}</h2>
                    <livewire:folder-tree :model-type="\FluxErp\Models\Ticket::class" :model-id="$ticket['id']" />
                </div>
            </div>
        </div>
        <x-tabs
            wire:model="tab"
            :tabs="[
                'features.comments.comments' => __('Comments'),
                'features.activities' => __('Activities'),
            ]"
        >
            <livewire:is wire:key="{{ uniqid() }}" :component="$tab" :model-type="\FluxErp\Models\Ticket::class" :model-id="$ticket['id']" />
        </x-tabs>
    </div>
</div>
