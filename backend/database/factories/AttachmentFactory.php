<?php

namespace Database\Factories;

use App\Features\Attachments\Models\Attachment;
use App\Features\Edits\Models\Edit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Attachment>
     */
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'edit_id' => Edit::factory(),
            'file_path' => 'attachments/'.now()->format('Y/m').'/'.Str::random(40).'.pdf',
            'original_file_name' => fake()->word().'.pdf',
            'description' => fake()->optional()->sentence(),
        ];
    }
}
