<?php

namespace FluxErp\Database\Factories;

use DateTime;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        /** @var DateTime $startDate */
        $startDate = $this->faker->dateTimeBetween(
            now()->subMonths(2)->startOfMonth(),
            now()->addMonths()->endOfMonth()
        );

        return [
            'name' => $this->faker->jobTitle(),
            'description' => $this->faker->realText(),
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'due_date' => $this->faker->boolean(75)
                ? Carbon::instance($startDate)->addDays(rand(1, 3))->format('Y-m-d H:i:s')
                : null,
            'priority' => rand(0, 5),
            'time_budget' => rand(0, 1000) . ':' . rand(0, 59),
            'budget' => $this->faker->randomFloat(),
        ];
    }
}
