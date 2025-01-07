<?php

namespace Kubectyl\Tests\Unit\Http\Middleware;

use Kubectyl\Tests\TestCase;
use Kubectyl\Tests\Traits\Http\RequestMockHelpers;
use Kubectyl\Tests\Traits\Http\MocksMiddlewareClosure;
use Kubectyl\Tests\Assertions\MiddlewareAttributeAssertionsTrait;

abstract class MiddlewareTestCase extends TestCase
{
    use MiddlewareAttributeAssertionsTrait;
    use MocksMiddlewareClosure;
    use RequestMockHelpers;

    /**
     * Setup tests with a mocked request object and normal attributes.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->buildRequestMock();
    }
}
