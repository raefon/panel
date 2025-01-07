<?php

namespace Database\Factories;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Kubectyl\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Server::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'uuidShort' => Str::lower(Str::random(8)),
            'name' => $this->faker->firstName,
            'description' => implode(' ', $this->faker->sentences()),
            'skip_scripts' => 0,
            'status' => null,
            'memory_request' => 256,
            'memory_limit' => 512,
            'disk' => 512,
            'cpu_request' => 13,
            'cpu_limit' => 100,
            'startup' => '/bin/bash echo "hello world"',
            'image' => 'foo/bar:latest',
            'default_port' => '65535',
            'allocation_limit' => null,
            'database_limit' => null,
            'node_selectors' => null,
            'snapshot_limit' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
