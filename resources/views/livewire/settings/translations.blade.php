<div
    class="py-6"
    x-data="{
        translations: $wire.entangle('translations'),
        locale: @entangle('locale').live,
    }"
>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">
                    {{ __('Translations') }}
                </h1>
                <div class="mt-2 text-sm text-gray-300">
                    {{ __('Here you can manage your translations...') }}
                </div>
            </div>
            <div class="mt-6 sm:ml-16">
                <div class="flex items-center py-3">
                    <x-input
                        spinner
                        icon="magnifying-glass"
                        placeholder="{{ __('Search…') }}"
                        wire:model.live="search"
                    />
                </div>
            </div>
            <div class="sm:ml-16">
                <x-select.styled
                    :label="__('Language')"
                    wire:model.live="locale"
                    required
                    :options="$locales"
                />
            </div>
            <div class="mt-6 sm:ml-16">
                <x-button
                    color="indigo"
                    :text="__('Create')"
                    wire:click="show()"
                />
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div
                    class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                >
                    <div
                        class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
                    >
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr class="divide-x divide-gray-200">
                                    <th
                                        scope="col"
                                        class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                    >
                                        {{ __('Name') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    >
                                        {{ __('Translations') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="py-2 pl-2 pr-2 text-left text-sm font-semibold text-gray-900"
                                    ></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <template
                                    x-for="(translation, index) in translations"
                                >
                                    <tr class="divide-x divide-gray-200">
                                        <td
                                            x-text="translation.group + '.' + translation.key"
                                            class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6"
                                        />
                                        <td
                                            x-text="translation.text[locale]"
                                            class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6"
                                        />
                                        <td
                                            class="whitespace-nowrap py-2 pl-2 pr-2 text-center text-sm text-gray-500"
                                        >
                                            <x-button
                                                color="secondary"
                                                light
                                                x-on:click="$wire.show(index)"
                                                :text="__('Edit')"
                                            />
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal
        id="edit-translation-modal"
        z-index="z-30"
        wire="showTranslationModal"
        :title="$index === -1 ? __('Create Translation') : __('Edit Translation')"
    >
        <livewire:settings.translation-edit />
        <x-slot:footer>
            <div x-data="{ index: @entangle('index') }" class="w-full">
                <div class="flex justify-between gap-x-4">
                    @if (user_can('action.translation.delete'))
                        <x-button
                            color="red"
                            light
                            x-bind:class="index > -1 || 'invisible'"
                            flat
                            :text="__('Delete')"
                            wire:click="delete()"
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Translation')]) }}"
                        />
                    @endif

                    <div class="flex gap-x-2">
                        <x-button
                            color="secondary"
                            light
                            flat
                            :text="__('Cancel')"
                            x-on:click="$modalClose('edit-translation-modal')"
                        />
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            wire:click="$dispatchTo('settings.translation-edit', 'save')"
                        />
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal>
</div>
