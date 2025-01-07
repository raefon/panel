<?php

namespace Kubectyl\Tests\Integration\Api\Application\Launchpads;

use Illuminate\Support\Arr;
use Kubectyl\Models\Rocket;
use Illuminate\Http\Response;
use Kubectyl\Transformers\Api\Application\RocketTransformer;
use Kubectyl\Tests\Integration\Api\Application\ApplicationApiIntegrationTestCase;

class RocketControllerTest extends ApplicationApiIntegrationTestCase
{
    /**
     * Test that all the rockets belonging to a given launchpad can be returned.
     */
    public function testListAllRocketsInLaunchpad()
    {
        $rockets = Rocket::query()->where('launchpad_id', 1)->get();

        $response = $this->getJson('/api/application/launchpads/' . $rockets->first()->launchpad_id . '/rockets');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(count($rockets), 'data');
        $response->assertJsonStructure([
            'object',
            'data' => [
                [
                    'object',
                    'attributes' => [
                        'id', 'uuid', 'launchpad', 'author', 'description', 'docker_image', 'startup', 'created_at', 'updated_at',
                        'script' => ['privileged', 'install', 'entry', 'container', 'extends'],
                        'config' => [
                            'files' => [],
                            'startup' => ['done'],
                            'stop',
                            'logs' => [],
                            'extends',
                        ],
                    ],
                ],
            ],
        ]);

        foreach (array_get($response->json(), 'data') as $datum) {
            $rocket = $rockets->where('id', '=', $datum['attributes']['id'])->first();

            $expected = json_encode(Arr::sortRecursive($datum['attributes']));
            $actual = json_encode(Arr::sortRecursive($this->getTransformer(RocketTransformer::class)->transform($rocket)));

            $this->assertSame(
                $expected,
                $actual,
                'Unable to find JSON fragment: ' . PHP_EOL . PHP_EOL . "[$expected]" . PHP_EOL . PHP_EOL . 'within' . PHP_EOL . PHP_EOL . "[$actual]."
            );
        }
    }

    /**
     * Test that a single rocket can be returned.
     */
    public function testReturnSingleRocket()
    {
        $rocket = Rocket::query()->findOrFail(1);

        $response = $this->getJson('/api/application/launchpads/' . $rocket->launchpad_id . '/rockets/' . $rocket->id);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'object',
            'attributes' => [
                'id', 'uuid', 'launchpad', 'author', 'description', 'docker_image', 'startup', 'script' => [], 'config' => [], 'created_at', 'updated_at',
            ],
        ]);

        $response->assertJson([
            'object' => 'rocket',
            'attributes' => $this->getTransformer(RocketTransformer::class)->transform($rocket),
        ], true);
    }

    /**
     * Test that a single rocket and all the defined relationships can be returned.
     */
    public function testReturnSingleRocketWithRelationships()
    {
        $rocket = Rocket::query()->findOrFail(1);

        $response = $this->getJson('/api/application/launchpads/' . $rocket->launchpad_id . '/rockets/' . $rocket->id . '?include=servers,variables,launchpad');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'object',
            'attributes' => [
                'relationships' => [
                    'launchpad' => ['object', 'attributes'],
                    'servers' => ['object', 'data' => []],
                    'variables' => ['object', 'data' => []],
                ],
            ],
        ]);
    }

    /**
     * Test that a missing rocket returns a 404 error.
     */
    public function testGetMissingRocket()
    {
        $rocket = Rocket::query()->findOrFail(1);

        $response = $this->getJson('/api/application/launchpads/' . $rocket->launchpad_id . '/rockets/nil');
        $this->assertNotFoundJson($response);
    }

    /**
     * Test that an authentication error occurs if a key does not have permission
     * to access a resource.
     */
    public function testErrorReturnedIfNoPermission()
    {
        $rocket = Rocket::query()->findOrFail(1);
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_rockets' => 0]);

        $response = $this->getJson('/api/application/launchpads/' . $rocket->launchpad_id . '/rockets');
        $this->assertAccessDeniedJson($response);
    }
}
