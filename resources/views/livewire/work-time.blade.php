<div x-data="{
    currentWorkTime: $wire.entangle('workTime'),
    time: 0,
    open: false,
    activeWorkTimes: $wire.entangle('activeWorkTimes'),
    init() {
        this.activeWorkTimes.forEach((workTime) => {
            if (! workTime.interval) {
                this.startTimer(workTime);
            }
            this.time = this.activeWorkTimes.reduce((acc, workTime) => {
                if (! workTime.interval) {
                    this.startTimer(workTime);
                }
                return this.calculateTime(workTime) + acc;
            }, 0);
        });

        this.$watch('activeWorkTimes', (value) => {
            this.time = value.reduce((acc, workTime) => {
                if (! workTime.interval) {
                    this.startTimer(workTime);
                }

                return this.calculateTime(workTime) + acc;
            }, 0);
        });
    },
    calculateTime(workTime) {
        let diff = (workTime.ended_at ? new Date(workTime.ended_at) : new Date()) - new Date(workTime.started_at);

        return diff - workTime.paused_time_ms;
    },
    startTimer(workTime) {
        if (workTime.ended_at) {
            return;
        }

        workTime.interval = setInterval(() => {
            this.time += 1000;
            document.querySelector(`#active-work-times [data-id='${workTime.id}']`).innerHTML = this.msTimeToString(this.calculateTime(workTime));
        }, 1000);
    },
    msTimeToString(time) {
        let seconds = Math.floor(time / 1000);
        let minutes = Math.floor(seconds / 60);
        seconds = seconds % 60;
        let hours = Math.floor(minutes / 60);
        minutes = minutes % 60;

        hours = hours.toString().padStart(2, '0');
        minutes = minutes.toString().padStart(2, '0');
        seconds = seconds.toString().padStart(2, '0');

        return `${hours}:${minutes}:${seconds}`;
    },
    stopWorkDay() {
        this.activeWorkTimes.forEach((workTime) => {
            clearInterval(workTime.interval);
        });
        $wire.toggleWorkDay(false);
        this.time = 0;
    },
    stopWorkTime(workTime) {
        clearInterval(workTime.interval);
        $wire.stop(workTime.id).then((response) => {
            if(response) {
                this.time = Math.max(this.time - this.calculateTime(workTime), 0);
            }
        });
    },
    pauseWorkTime(workTime) {
        clearInterval(workTime.interval);
        $wire.pause(workTime.id);
    },
    continueWorkTime(workTime) {
        $wire.continue(workTime.id).then((response) => {
            if(response) {
                workTime.ended_at = null;
            }
        });
    },
    relatedSelected(value) {
        let searchRoute = {{  '\'' . route('search', '__model__') . '\'' }}
        searchRoute = searchRoute.replace('__model__', value);
        $wire.trackable_id = null;
        Alpine.$data(document.getElementById('trackable-id').querySelector('[x-data]')).asyncData.api = searchRoute;
    },
}">
    <x-modal name="work-time" persistent="true">
        <x-card class="flex flex-col gap-4">
            <x-select :label="__('Work Time Type')" :options="$workTimeTypes" wire:model="workTime.work_time_type_id" option-value="id" option-label="name"/>
            <x-select :label="__('Contact')"
                wire:model="workTime.contact_id"
                option-value="id"
                option-label="label"
                template="user-option"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
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
            <x-select x-on:selected="relatedSelected($event.detail.value)" :label="__('Model')" :options="$trackableTypes" wire:model="workTime.trackable_type" />
            <div id="trackable-id" x-show="$wire.workTime.trackable_type">
                <x-select :label="__('Record')"
                    option-value="id"
                    option-label="label"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Order::class),
                        'method' => 'POST',
                    ]"
                    wire:model="workTime.trackable_id"
                />
            </div>
            <x-input :label="__('Name')" wire:model="workTime.name" />
            <x-textarea :label="__('Description')" wire:model="workTime.description" />
            <x-slot:footer>
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button primary spinner x-on:click="$wire.save().then((response) => {if(response) close();})" :label="__('Start')" />
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <x-button
        rounded
        primary
        x-on:click="open = ! open"
        x-ref="button"
        x-bind:class="$wire.workTime.is_pause && 'ring-warning-500 text-white bg-warning-500 hover:bg-warning-600 hover:ring-warning-600 dark:ring-offset-slate-800 dark:bg-warning-700 dark:ring-warning-700 dark:hover:bg-warning-600 dark:hover:ring-warning-600'"
        icon="clock"
    >
        <div x-text="msTimeToString(time)"></div>
    </x-button>
    <div x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-show="open"
         x-anchor.bottom-end.offset.5="$refs.button"
         class="z-30"
    >
        <x-card id="active-work-times" class="flex flex-col gap-4" :title="__('Active Work Times')">
            <div class="flex w-full gap-1.5">
                <x-button class="w-full" x-show="! $wire.dailyWorkTime.id" positive :label="__('Start Workday')" x-on:click="$wire.toggleWorkDay(true)" />
                <x-button class="w-1/2" x-show="$wire.dailyWorkTime.id" negative :label="__('End Workday')" x-on:click="stopWorkDay()" />
                <x-button class="w-1/2" x-show="$wire.dailyWorkTime.id && ! $wire.dailyWorkTimePause.id" warning :label="__('Pause')" x-on:click="$wire.togglePauseWorkDay(true)" />
                <x-button class="w-1/2" x-show="$wire.dailyWorkTime.id && $wire.dailyWorkTimePause.id" positive :label="__('Continue')" x-on:click="$wire.togglePauseWorkDay(false)" />
            </div>
            <x-button x-show="$wire.dailyWorkTime.id" positive :label="__('Record new working hours')" x-on:click="$openModal('work-time')" />
            <template x-for="workTime in activeWorkTimes">
                <div class="odd:bg-neutral-100 rounded-md p-1.5 flex flex-col gap-1.5">
                    <div class="flex justify-between w-full">
                        <div class="flex flex-col w-full">
                            <div class="text-gray-500 dark:text-gray-400" x-text="workTime.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="workTime.work_time_type?.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatters.datetime(workTime.started_at)"></div>
                            <x-badge primary>
                                <div x-bind:data-id="workTime.id" x-init="$el.innerText = msTimeToString(calculateTime(workTime))">
                                </div>
                            </x-badge>
                        </div>
                    </div>
                    <div class="flex justify-end gap-x-4">
                        <x-button class="w-1/2" x-show="! workTime.ended_at" warning icon="pause" :label="__('Pause')" x-on:click="pauseWorkTime(workTime)" />
                        <x-button class="w-1/2" x-show="workTime.ended_at" positive icon="play" :label="__('Continue')" x-on:click="continueWorkTime(workTime)" />
                        <x-button class="w-1/2" negative icon="stop" :label="__('Stop')" x-on:click="stopWorkTime(workTime)" />
                    </div>
                </div>
            </template>
        </x-card>
    </div>
</div>
