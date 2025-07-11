<div x-data="dashboard()" x-init.once="reInit().disable()">
    @section('dashboard-widget-select')
    @if ($this->canEdit)
        <x-flux::dashboard.widget-select />
    @endif

    @show
    <div class="mx-auto items-center justify-between py-6 md:flex">
        @section('dashboard-header')
        <div></div>
        @show
        @section('dashboard-edit')
        <x-flux::dashboard.edit-dashboard
            :can-edit="$this->canEdit"
            :has-time-selector="$this->hasTimeSelector"
        />
        @show
    </div>
    <x-flux::dashboard.grid />
</div>
