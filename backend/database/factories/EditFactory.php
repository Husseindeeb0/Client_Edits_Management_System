<?php

namespace Database\Factories;

use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;
use App\Features\Edits\Models\Edit;
use App\Features\Projects\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Edit>
 */
class EditFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Edit>
     */
    protected $model = Edit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(EditPriority::cases()),
            'status' => fake()->randomElement(EditStatus::cases()),
            'price' => fake()->optional()->randomFloat(2, 10, 5000),
            'is_paid' => fake()->boolean(),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+3 months')?->format('Y-m-d'),
        ];
    }

    /**
     * An edit whose status still counts as active work.
     */
    public function active(): static
    {
        return $this->state(fn () => ['status' => EditStatus::Pending]);
    }

    /**
     * An edit whose work is finished.
     */
    public function done(): static
    {
        return $this->state(fn () => ['status' => EditStatus::Done]);
    }
}
