<?php

namespace Kubectyl\Tests\Integration\Services\Servers;

use Kubectyl\Models\User;
use Kubectyl\Models\Rocket;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Kubectyl\Tests\Integration\IntegrationTestCase;
use Kubectyl\Services\Servers\VariableValidatorService;

class VariableValidatorServiceTest extends IntegrationTestCase
{
    protected Rocket $rocket;

    public function setUp(): void
    {
        parent::setUp();

        /* @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->rocket = Rocket::query()
            ->where('author', 'support@kubectyl.org')
            ->where('name', 'Bungeecord')
            ->firstOrFail();
    }

    /**
     * Test that environment variables for a server are validated as expected.
     */
    public function testEnvironmentVariablesCanBeValidated()
    {
        $rocket = $this->cloneRocketAndVariables($this->rocket);

        try {
            $this->getService()->handle($rocket->id, [
                'BUNGEE_VERSION' => '1.2.3',
            ]);

            $this->fail('This statement should not be reached.');
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            $this->assertCount(2, $errors);
            $this->assertArrayHasKey('environment.BUNGEE_VERSION', $errors);
            $this->assertArrayHasKey('environment.SERVER_JARFILE', $errors);
            $this->assertSame('The Bungeecord Version variable may only contain letters and numbers.', $errors['environment.BUNGEE_VERSION'][0]);
            $this->assertSame('The Bungeecord Jar File variable field is required.', $errors['environment.SERVER_JARFILE'][0]);
        }

        $response = $this->getService()->handle($rocket->id, [
            'BUNGEE_VERSION' => '1234',
            'SERVER_JARFILE' => 'server.jar',
        ]);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(2, $response);
        $this->assertSame('BUNGEE_VERSION', $response->get(0)->key);
        $this->assertSame('1234', $response->get(0)->value);
        $this->assertSame('SERVER_JARFILE', $response->get(1)->key);
        $this->assertSame('server.jar', $response->get(1)->value);
    }

    /**
     * Test that variables that are user_editable=false do not get validated (or returned) by
     * the handler.
     */
    public function testNormalUserCannotValidateNonUserEditableVariables()
    {
        $rocket = $this->cloneRocketAndVariables($this->rocket);
        $rocket->variables()->first()->update([
            'user_editable' => false,
        ]);

        $response = $this->getService()->handle($rocket->id, [
            // This is an invalid value, but it shouldn't cause any issues since it should be skipped.
            'BUNGEE_VERSION' => '1.2.3',
            'SERVER_JARFILE' => 'server.jar',
        ]);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(1, $response);
        $this->assertSame('SERVER_JARFILE', $response->get(0)->key);
        $this->assertSame('server.jar', $response->get(0)->value);
    }

    public function testEnvironmentVariablesCanBeUpdatedAsAdmin()
    {
        $rocket = $this->cloneRocketAndVariables($this->rocket);
        $rocket->variables()->first()->update([
            'user_editable' => false,
        ]);

        try {
            $this->getService()->setUserLevel(User::USER_LEVEL_ADMIN)->handle($rocket->id, [
                'BUNGEE_VERSION' => '1.2.3',
                'SERVER_JARFILE' => 'server.jar',
            ]);

            $this->fail('This statement should not be reached.');
        } catch (ValidationException $exception) {
            $this->assertCount(1, $exception->errors());
            $this->assertArrayHasKey('environment.BUNGEE_VERSION', $exception->errors());
        }

        $response = $this->getService()->setUserLevel(User::USER_LEVEL_ADMIN)->handle($rocket->id, [
            'BUNGEE_VERSION' => '123',
            'SERVER_JARFILE' => 'server.jar',
        ]);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(2, $response);
        $this->assertSame('BUNGEE_VERSION', $response->get(0)->key);
        $this->assertSame('123', $response->get(0)->value);
        $this->assertSame('SERVER_JARFILE', $response->get(1)->key);
        $this->assertSame('server.jar', $response->get(1)->value);
    }

    public function testNullableEnvironmentVariablesCanBeUsedCorrectly()
    {
        $rocket = $this->cloneRocketAndVariables($this->rocket);
        $rocket->variables()->where('env_variable', '!=', 'BUNGEE_VERSION')->delete();

        $rocket->variables()->update(['rules' => 'nullable|string']);

        $response = $this->getService()->handle($rocket->id, []);
        $this->assertCount(1, $response);
        $this->assertNull($response->get(0)->value);

        $response = $this->getService()->handle($rocket->id, ['BUNGEE_VERSION' => null]);
        $this->assertCount(1, $response);
        $this->assertNull($response->get(0)->value);

        $response = $this->getService()->handle($rocket->id, ['BUNGEE_VERSION' => '']);
        $this->assertCount(1, $response);
        $this->assertSame('', $response->get(0)->value);
    }

    private function getService(): VariableValidatorService
    {
        return $this->app->make(VariableValidatorService::class);
    }
}
