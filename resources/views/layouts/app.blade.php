<!DOCTYPE html>
@props(['navigation' => request()->boolean('no-navigation')])
<html @class([
        'sort-scrollbar',
        'h-full',
        'text-sm',
        'dark' => auth()->check() && auth()->user()->is_dark_mode,
    ]
) lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? config('app.name', 'Flux ERP') }}</title>
    <x-flux::layouts.head.head/>
</head>
<body class="dark:bg-secondary-900 h-full bg-slate-50 transition duration-300 text-secondary-600 dark:text-secondary-50">
    @section('wire.navigate.spinner')
        @persist('spinner')
            <div id="loading-overlay" class="fixed inset-0 overflow-y-auto p-4 hidden" style="z-index: 1000;">
                <div id="loading-overlay-spinner" class="fixed inset-0 bg-secondary-400 dark:bg-secondary-700 bg-opacity-60 dark:bg-opacity-60 flex items-center justify-center transition-opacity opacity-0 duration-200">
                    <x-flux::spinner-svg />
                </div>
            </div>
        @endpersist
    @show
    @section('layout.app.body')
        @persist('notifications')
            <x-notifications z-index="z-50"></x-notifications>
            <x-dialog z-index="z-40" blur="md" align="center"/>
            <x-dialog z-index="z-40" blur="md" align="center" id="prompt">
                <x-input id="prompt-value" />
            </x-dialog>
        @endpersist
        <x-flux::flash />
        @auth('web')
            @persist('mail')
                <div id="mail">
                    <livewire:edit-mail lazy />
                </div>
                <x-modal
                    name="detail-modal"
                    max-width="7xl"
                    x-on:close="$el.querySelector('iframe').src = 'data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E'"
                >
                    <div
                        class="w-full"
                        x-data="{
                            openUrl() {
                                let urlObj = new URL($el.querySelector('iframe').src);
                                urlObj.searchParams.delete('no-navigation');

                                window.open(urlObj);
                                close();
                            }
                        }"
                    >
                        <x-card class="grid h-screen">
                            <iframe class="object-contain" height="100%" width="100%" id="detail-modal-iframe" src="data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E">
                            </iframe>
                            <x-slot:footer>
                                <div class="w-full flex justify-end gap-1.5">
                                    <x-button :label="__('Cancel')" x-on:click="close"/>
                                    <x-button primary :label="__('Open')" x-on:click="openUrl()"/>
                                </div>
                            </x-slot:footer>
                        </x-card>
                    </div>
                </x-modal>
            @endpersist
        @endauth
        @section('layout.app.body.content')
            <div x-data="{ open: false }" x-on:keydown.window.escape="open = false" class="flex h-screen w-full flex-col">
                @section('layout.app.body.content.navigation')
                    @if(auth()->check() && method_exists(auth()->guard(), 'getName') && ! $navigation)
                        @php($navigation = true)
                        @persist('navigation')
                            <div id="nav">
                                <livewire:navigation />
                            </div>
                        @endpersist
                    @endif
                @show
                @section('layout.app.body.content.main')
                    <div @if($navigation) class="md:pl-20" @endif>
                        <main @if($navigation) class="px-1.5 md:px-8 pb-1.5 md:pb-8" @endif>
                            {{ $slot }}
                        </main>
                    </div>
                @show
            </div>
        @show
    @show
</body>
</html>
