<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Task\TaskState;

class UpdateTaskRuleset extends FluxRuleset
{
    protected static ?string $model = Task::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(UserRuleset::class, 'getRules'),
            resolve_static(OrderPositionRuleset::class, 'getRules'),
            resolve_static(CategoryRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Task::class]),
            ],
            'project_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Project::class]),
            ],
            'responsible_user_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'order_position_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'name' => 'sometimes|required|string',
            'description' => 'string|nullable',
            'start_date' => 'present|date|nullable',
            'due_date' => 'present|date|nullable|after_or_equal:start_date',
            'priority' => 'sometimes|required|integer|min:0',
            'state' => [
                'string',
                ValidStateRule::make(TaskState::class),
            ],
            'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
            'budget' => 'numeric|nullable|min:0',
        ];
    }
}
