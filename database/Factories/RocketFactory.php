<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use Kubectyl\Models\Rocket;
use Illuminate\Database\Eloquent\Factories\Factory;

class RocketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rocket::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'name' => $this->faker->name,
            'description' => implode(' ', $this->faker->sentences()),
            'startup' => 'java -jar test.jar',
        ];
    }
}
