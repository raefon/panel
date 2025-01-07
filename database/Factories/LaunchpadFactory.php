<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use Kubectyl\Models\Launchpad;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaunchpadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Launchpad::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'author' => 'testauthor@example.com',
            'name' => $this->faker->word,
            'description' => null,
        ];
    }
}
