<?php

namespace Database\Factories;

use App\Features\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Client>
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone_number' => fake()->numerify('+1##########'),
            'email' => fake()->optional()->safeEmail(),
            'last_follow_up_date' => fake()->optional()->date(),
        ];
    }
}
