<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateProjectRequest;
use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProject extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProjectRequest())->rules();
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function performAction(): Model
    {
        $project = Project::query()
            ->whereKey($this->data['id'])
            ->first();

        $project->fill($this->data);
        $project->save();

        return $project->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Project());

        $this->data = $validator->validate();

        if (array_key_exists('categories', $this->data)) {
            if (is_array($this->data['categories'][0])) {
                $intArray = Arr::pluck($this->data['categories'], 'id');
            } else {
                $intArray = array_filter($this->data['categories'], function ($value) {
                    return is_numeric($value) && $value > 0;
                });
                $intArray = array_map('intval', $intArray);
            }

            $project = Project::query()
                ->whereKey($this->data['id'])
                ->with(['tasks' => ['categories:id'], 'categories:id'])
                ->first();

            $projectCategories = $project
                ->category
                ?->children()
                ->with('children:id,parent_id')
                ->get()
                ->toArray();
            $categories = $projectCategories ? array_column(to_flat_tree($projectCategories), 'id') : [];

            $diff = array_diff($intArray, $categories);
            if (count($diff) > 0) {
                throw ValidationException::withMessages([
                    'categories' => [
                        __(
                            'categories \':values\' not found',
                            ['values' => implode(', ', array_values($diff))]
                        ),
                    ],
                ])->errorBag('updateProject');
            }

            $projectTaskCategories = [];
            $project->tasks->each(function ($task) use (&$projectTaskCategories) {
                $projectTaskCategories = array_merge(
                    $projectTaskCategories,
                    $task->categories->pluck('id')->toArray()
                );
            });

            if (! empty(array_diff($projectTaskCategories, $intArray))) {
                throw ValidationException::withMessages([
                    'categories' => [
                        __('Project task with different category exists'),
                    ],
                ])->errorBag('updateProject');
            }
        }
    }
}
