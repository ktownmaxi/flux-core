<div
    class="flow-root"
    x-data="{
        init() {
            $wire.loadData()
        },
        activeActivity: null,
        page: $wire.entangle('page', true),
        total: $wire.entangle('total', true),
        perPage: $wire.entangle('perPage', true),
        showProperties(id) {
            this.activeActivity = this.activeActivity === id ? null : id
        },
    }"
>
    <x-spinner />
    <ul role="list" class="-mb-1">
        <template
            x-for="(activity, index) in $wire.activities"
            :key="activity.id"
        >
            <li class="p-2">
                <div class="relative pb-1">
                    <div
                        x-bind:class="
                            activity.id === activeActivity &&
                                'border-gray-200 dark:border-secondary-500 rounded-md border'
                        "
                    >
                        <div class="relative flex space-x-3 p-1.5">
                            <div>
                                <div
                                    x-init="
                                        $nextTick(() => {
                                            $el.querySelector('img').src = activity.causer.avatar_url
                                        })
                                    "
                                >
                                    <x-avatar
                                        sm
                                        image="{{ route('icons', ['name' => 'user']) }}"
                                        xl
                                    />
                                </div>
                            </div>
                            <div
                                class="flex min-w-0 flex-1 justify-between space-x-4"
                            >
                                <div>
                                    <div class="text-sm text-gray-500">
                                        <span
                                            x-text="activity.causer.name"
                                        ></span>
                                        <span
                                            x-on:click="showProperties(activity.id)"
                                            href="#"
                                            class="cursor-pointer font-medium text-gray-900 dark:text-white"
                                            x-text="activity.event"
                                        ></span>
                                        <div
                                            x-show="activity.id === activeActivity"
                                            x-collapse
                                            x-cloak
                                        >
                                            <div
                                                x-text="activity.description"
                                            ></div>
                                            <template
                                                x-for="
                                                    (value, name) in
                                                        Object.fromEntries(Object.entries(activity.properties.attributes ?? {}))
                                                "
                                            >
                                                <div>
                                                    <span
                                                        class="font-semibold"
                                                        x-text="name + ':'"
                                                    ></span>
                                                    <span
                                                        x-html="
                                                            activity.properties.old && activity.properties.old[name]
                                                                ? activity.properties.old[name] + '<span> -></span>'
                                                                : ''
                                                        "
                                                    ></span>
                                                    <span
                                                        x-text="typeof value === 'undefined' ? '' : value"
                                                    ></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="whitespace-nowrap text-right text-sm text-gray-500"
                                >
                                    <time
                                        x-text="formatters.datetime(activity.created_at)"
                                    ></time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </template>
        <template x-if="perPage < total">
            <x-button
                x-on:click="page++"
                color="indigo"
                class="w-full"
                :text="__('Show more')"
            ></x-button>
        </template>
    </ul>
</div>
