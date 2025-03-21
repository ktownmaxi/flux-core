<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\AdditionalColumnList;
use FluxErp\Models\AdditionalColumn;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AdditionalColumns extends AdditionalColumnList
{
    public bool $create = true;

    public bool $showAdditionalColumnModal = false;

    protected ?string $includeBefore = 'flux::livewire.settings.additional-columns';

    protected $listeners = [
        'closeModal',
    ];

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.show(record.id)',
                ]),
        ];
    }

    public function closeModal(): void
    {
        $this->loadData();

        $this->showAdditionalColumnModal = false;
        $this->skipRender();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.additional-column-edit');
    }

    public function show(?AdditionalColumn $record = null): void
    {
        $this->dispatch('show', $record?->toArray())->to('settings.additional-column-edit');

        $this->create = ! $record->exists;
        $this->showAdditionalColumnModal = true;
    }
}
