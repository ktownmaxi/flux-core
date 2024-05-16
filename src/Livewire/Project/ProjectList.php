<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Livewire\DataTables\ProjectList as BaseProjectList;
use FluxErp\Livewire\Forms\ProjectForm;
use FluxErp\Models\Project;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProjectList extends BaseProjectList
{
    protected ?string $includeBefore = 'flux::livewire.project.project-list';

    public array $availableStates = [];

    public ProjectForm $project;

    public function mount(): void
    {
        parent::mount();

        $this->project->additionalColumns = array_fill_keys(
            resolve_static(Project::class, 'additionalColumnsQuery')->pluck('name')?->toArray() ?? [],
            null
        );

        $this->availableStates = app(Project::class)->getStatesFor('state')->map(function ($state) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $state))),
                'name' => $state,
            ];
        })->toArray();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Create'))
                ->icon('plus')
                ->wireClick('createProject')
                ->when(fn () => resolve_static(CreateProject::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->project->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function createProject(): void
    {
        $this->project->reset();
        $this->project->additionalColumns = array_fill_keys(
            resolve_static(Project::class, 'additionalColumnsQuery')
                ->pluck('name')
                ?->toArray() ?? [],
            null
        );

        $this->js(<<<'JS'
            $openModal('edit-project');
        JS);
    }
}
