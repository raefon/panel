<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use Carbon\CarbonImmutable;
use Kubectyl\Models\Snapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class SnapshotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Snapshot::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'name' => $this->faker->sentence,
            'disk' => Snapshot::ADAPTER_KUBER,
            'is_successful' => true,
            'created_at' => CarbonImmutable::now(),
            'completed_at' => CarbonImmutable::now(),
        ];
    }
}
