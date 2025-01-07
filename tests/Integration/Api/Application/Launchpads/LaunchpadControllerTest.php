<?php

namespace Kubectyl\Tests\Integration\Api\Application\Launchpads;

use Illuminate\Http\Response;
use Kubectyl\Contracts\Repository\LaunchpadRepositoryInterface;
use Kubectyl\Transformers\Api\Application\LaunchpadTransformer;
use Kubectyl\Tests\Integration\Api\Application\ApplicationApiIntegrationTestCase;

class LaunchpadControllerTest extends ApplicationApiIntegrationTestCase
{
    private LaunchpadRepositoryInterface $repository;

    /**
     * Setup tests.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(LaunchpadRepositoryInterface::class);
    }

    /**
     * Test that the expected launchpads are returned by the request.
     */
    public function testLaunchpadResponse()
    {
        /** @var \Kubectyl\Models\Launchpad[] $launchpads */
        $launchpads = $this->repository->all();

        $response = $this->getJson('/api/application/launchpads');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(count($launchpads), 'data');
        $response->assertJsonStructure([
            'object',
            'data' => [['object', 'attributes' => ['id', 'uuid', 'author', 'name', 'description', 'created_at', 'updated_at']]],
            'meta' => ['pagination' => ['total', 'count', 'per_page', 'current_page', 'total_pages']],
        ]);

        $response->assertJson([
            'object' => 'list',
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total' => 4,
                    'count' => 4,
                    'per_page' => 50,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
        ]);

        foreach ($launchpads as $launchpad) {
            $response->assertJsonFragment([
                'object' => 'launchpad',
                'attributes' => $this->getTransformer(LaunchpadTransformer::class)->transform($launchpad),
            ]);
        }
    }

    /**
     * Test that getting a single launchpad returns the expected result.
     */
    public function testSingleLaunchpadResponse()
    {
        $launchpad = $this->repository->find(1);

        $response = $this->getJson('/api/application/launchpads/' . $launchpad->id);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'object',
            'attributes' => ['id', 'uuid', 'author', 'name', 'description', 'created_at', 'updated_at'],
        ]);

        $response->assertJson([
            'object' => 'launchpad',
            'attributes' => $this->getTransformer(LaunchpadTransformer::class)->transform($launchpad),
        ]);
    }

    /**
     * Test that including rockets in the response works as expected.
     */
    public function testSingleLaunchpadWithRocketsIncluded()
    {
        $launchpad = $this->repository->find(1);
        $launchpad->loadMissing('rockets');

        $response = $this->getJson('/api/application/launchpads/' . $launchpad->id . '?include=servers,rockets');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'object',
            'attributes' => [
                'relationships' => [
                    'rockets' => ['object', 'data' => []],
                    'servers' => ['object', 'data' => []],
                ],
            ],
        ]);

        $response->assertJsonCount(count($launchpad->getRelation('rockets')), 'attributes.relationships.rockets.data');
    }

    /**
     * Test that a missing launchpad returns a 404 error.
     */
    public function testGetMissingLaunchpad()
    {
        $response = $this->getJson('/api/application/launchpads/nil');
        $this->assertNotFoundJson($response);
    }

    /**
     * Test that an authentication error occurs if a key does not have permission
     * to access a resource.
     */
    public function testErrorReturnedIfNoPermission()
    {
        $launchpad = $this->repository->find(1);
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_launchpads' => 0]);

        $response = $this->getJson('/api/application/launchpads/' . $launchpad->id);
        $this->assertAccessDeniedJson($response);
    }
}
