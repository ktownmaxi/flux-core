<div>
    <x-modal name="create-task-modal">
        <x-card :title="__('Create Task')">
            <div
                class="space-y-8 divide-y divide-gray-200"
                x-data="{
                    formatter: @js(resolve_static(\FluxErp\Models\Task::class, 'typeScriptAttributes'))
                }"
            >
                <div class="space-y-2.5">
                    <x-input wire:model="task.name" label="{{ __('Name') }}" />
                    <x-select
                        :label="__('Project')"
                        wire:model="task.project_id"
                        option-value="id"
                        option-label="label"
                        option-description="description"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Project::class),
                            'method' => 'POST',
                        ]"
                    />
                    <x-select
                        :label="__('Responsible User')"
                        option-value="id"
                        option-label="label"
                        autocomplete="off"
                        wire:model="task.responsible_user_id"
                        :template="[
                            'name'   => 'user-option',
                        ]"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\User::class),
                            'method' => 'POST',
                            'params' => [
                                'with' => 'media',
                            ],
                        ]"
                    />
                    <div class="flex justify-between gap-x-4">
                        <x-input type="date" wire:model="task.start_date" label="{{ __('Start Date') }}" />
                        <x-input type="date" wire:model="task.due_date" label="{{ __('Due Date') }}" />
                    </div>
                    <x-state
                        class="w-full"
                        align="left"
                        :label="__('Task state')"
                        wire:model="task.state"
                        formatters="formatter.state"
                        available="availableStates"
                    />
                    <x-inputs.number :label="__('Priority')" wire:model="task.priority" min="0" />
                    <x-textarea wire:model="task.description" label="{{ __('Description') }}" />
                    <x-select
                        :label="__('Categories')"
                        wire:model="task.categories"
                        multiselect
                        option-value="id"
                        option-label="label"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Category::class),
                            'method' => 'POST',
                            'params' => [
                                'where' => [
                                    [
                                        'model_type',
                                        '=',
                                        morph_alias(\FluxErp\Models\Task::class),
                                    ],
                                ],
                            ],
                        ]"
                    />
                    <x-select
                        :label="__('Assigned')"
                        option-value="id"
                        option-label="label"
                        autocomplete="off"
                        multiselect
                        wire:model="task.users"
                        :template="[
                            'name'   => 'user-option',
                        ]"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\User::class),
                            'method' => 'POST',
                            'params' => [
                                'with' => 'media',
                            ]
                        ]"
                    />
                    <x-inputs.number :label="__('Budget')" wire:model="task.budget" step="0.01" />
                    <x-input
                        :label="__('Time Budget')"
                        wire:model.blur="task.time_budget"
                        :corner-hint="__('Hours:Minutes')"
                        placeholder="02:30"
                    />
                </div>
                <div class="space-y-2.5">
                    <h3 class="font-medium whitespace-normal text-md text-secondary-700 dark:text-secondary-400 mt-4">
                        {{ __('Additional Columns') }}
                    </h3>
                    <x-flux::additional-columns :model="\FluxErp\Models\Task::class" :id="$this->task->id" wire="task.additionalColumns"/>
                </div>
            </div>
            <x-slot:footer>
                <div class="flex justify-end">
                    <x-button
                        flat
                        :label="__('Cancel')"
                        x-on:click="close()"
                    />
                    <x-button
                        primary
                        :label="__('Save')"
                        x-on:click="$wire.save().then((task) => {
                            if (task) {
                                close();
                            }
                        });"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <x-button
        primary
        x-on:click="$wire.resetTask(); $openModal('create-task-modal');"
        :label="__('Create Task')"
    />
</div>
