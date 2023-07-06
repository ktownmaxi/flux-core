<?php

namespace FluxErp\Actions\Project;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateProjectRequest;
use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProject implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateProjectRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'project.update';
    }

    public static function description(): string|null
    {
        return 'update project';
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function execute(): Model
    {
        $project = Project::query()
            ->whereKey($this->data['id'])
            ->first();

        $project->fill($this->data);
        $project->save();

        return $project->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Project());

        $this->data = $validator->validate();

        if (array_key_exists('categories', $this->data)) {
            if (is_array($this->data['categories'][0])) {
                $intArray = Arr::pluck($this->data['categories'], 'id');
            } else {
                $intArray = array_filter($this->data['categories'], function ($value) {
                    return is_int($value) && $value > 0;
                });
            }

            $project = Project::query()
                ->whereKey($this->data['id'])
                ->first();

            $categories = $project->categoryTemplate->categories->pluck('id')->toArray();

            $diff = array_diff($intArray, $categories);
            if (count($diff) > 0) {
                throw ValidationException::withMessages([
                    'categories' => [
                        __(
                            'categories \':values\' not found',
                            ['values' => implode(', ', array_values($diff))]
                        )
                    ],
                ])->errorBag('updateProject');
            }

            $projectTaskCategories = Arr::flatten(
                Arr::pluck(
                    $project->tasks()->get()->toArray(),
                    'category_id')
            );

            if (! empty(array_diff($projectTaskCategories, $intArray))) {
                throw ValidationException::withMessages([
                    'categories' => [
                        __('Project task with different category exists')
                    ],
                ])->errorBag('updateProject');
            }
        }

        return $this;
    }
}
