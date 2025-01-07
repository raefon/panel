<?php

namespace Kubectyl\Tests\Integration;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Kubectyl\Tests\TestCase;
use Kubectyl\Events\ActivityLogged;
use Illuminate\Support\Facades\Event;
use Kubectyl\Tests\Assertions\AssertsActivityLogged;
use Kubectyl\Tests\Traits\Integration\CreatesTestModels;
use Kubectyl\Transformers\Api\Application\BaseTransformer;

abstract class IntegrationTestCase extends TestCase
{
    use CreatesTestModels;
    use AssertsActivityLogged;

    protected array $connectionsToTransact = ['mysql'];

    protected $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    public function setUp(): void
    {
        parent::setUp();

        Event::fake(ActivityLogged::class);
    }

    /**
     * Return an ISO-8601 formatted timestamp to use in the API response.
     */
    protected function formatTimestamp(string $timestamp): string
    {
        return CarbonImmutable::createFromFormat(CarbonInterface::DEFAULT_TO_STRING_FORMAT, $timestamp)
            ->setTimezone(BaseTransformer::RESPONSE_TIMEZONE)
            ->toAtomString();
    }
}
