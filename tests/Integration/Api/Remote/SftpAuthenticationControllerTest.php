<?php

namespace Kubectyl\Tests\Integration\Api\Remote;

use Kubectyl\Models\User;
use Kubectyl\Models\Server;
use Kubectyl\Models\Cluster;
use Kubectyl\Models\Permission;
use Kubectyl\Models\UserSSHKey;
use phpseclib3\Crypt\EC\PrivateKey;
use Kubectyl\Tests\Integration\IntegrationTestCase;

class SftpAuthenticationControllerTest extends IntegrationTestCase
{
    protected User $user;

    protected Server $server;

    /**
     * Sets up the tests.
     */
    public function setUp(): void
    {
        parent::setUp();

        [$user, $server] = $this->generateTestAccount();

        $user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->user = $user;
        $this->server = $server;

        $this->setAuthorization();
    }

    /**
     * Test that a public key is validated correctly.
     */
    public function testPublicKeyIsValidatedCorrectly()
    {
        $key = UserSSHKey::factory()->for($this->user)->create();

        $this->postJson('/api/remote/sftp/auth', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.source_field', 'username')
            ->assertJsonPath('errors.0.meta.rule', 'required')
            ->assertJsonPath('errors.1.meta.source_field', 'password')
            ->assertJsonPath('errors.1.meta.rule', 'required');

        $data = [
            'type' => 'public_key',
            'username' => $this->getUsername(),
            'password' => $key->public_key,
        ];

        $this->postJson('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('server', $this->server->uuid)
            ->assertJsonPath('permissions', ['*']);

        $key->delete();
        $this->postJson('/api/remote/sftp/auth', $data)->assertForbidden();
        $this->postJson('/api/remote/sftp/auth', array_merge($data, ['type' => null]))->assertForbidden();
    }

    /**
     * Test that an account password is validated correctly.
     */
    public function testPasswordIsValidatedCorrectly()
    {
        $this->postJson('/api/remote/sftp/auth', [
            'username' => $this->getUsername(),
            'password' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.source_field', 'password')
            ->assertJsonPath('errors.0.meta.rule', 'required');

        $this->postJson('/api/remote/sftp/auth', [
            'username' => $this->getUsername(),
            'password' => 'wrong password',
        ])
            ->assertForbidden();

        $this->user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->postJson('/api/remote/sftp/auth', [
            'username' => $this->getUsername(),
            'password' => 'foobar',
        ])
            ->assertOk();
    }

    /**
     * Test that providing an invalid key and/or invalid username triggers the throttle on
     * the endpoint.
     *
     * @dataProvider authorizationTypeDataProvider
     */
    public function testUserIsThrottledIfInvalidCredentialsAreProvided()
    {
        for ($i = 0; $i <= 10; ++$i) {
            $this->postJson('/api/remote/sftp/auth', [
                'type' => 'public_key',
                'username' => $i % 2 === 0 ? $this->user->username : $this->getUsername(),
                'password' => 'invalid key',
            ])
                ->assertStatus($i === 10 ? 429 : 403);
        }
    }

    /**
     * Test that the user is not throttled so long as a valid public key is provided, even
     * if it doesn't actually exist in the database for the user.
     */
    // public function testUserIsNotThrottledIfNoPublicKeyMatches()
    // {
    //     for ($i = 0; $i <= 10; ++$i) {
    //         $this->postJson('/api/remote/sftp/auth', [
    //             'type' => 'public_key',
    //             'username' => $this->getUsername(),
    //             'password' => PrivateKey::createKey('Ed25519')->getPublicKey()->toString('OpenSSH'),
    //         ])
    //             ->assertForbidden();
    //     }
    // }

    /**
     * Test that a request is rejected if the credentials are valid but the username indicates
     * a server on a different cluster.
     *
     * @dataProvider authorizationTypeDataProvider
     */
    public function testRequestIsRejectedIfServerBelongsToDifferentCluster(string $type)
    {
        $cluster2 = $this->createServerModel()->cluster;

        $this->setAuthorization($cluster2);

        $password = $type === 'public_key'
            ? UserSSHKey::factory()->for($this->user)->create()->public_key
            : 'foobar';

        $this->postJson('/api/remote/sftp/auth', [
            'type' => 'public_key',
            'username' => $this->getUsername(),
            'password' => $password,
        ])
            ->assertForbidden();
    }

    public function testRequestIsDeniedIfUserLacksSftpPermission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_READ]);

        $user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->setAuthorization($server->cluster);

        $this->postJson('/api/remote/sftp/auth', [
            'username' => $user->username . '.' . $server->uuidShort,
            'password' => 'foobar',
        ])
            ->assertForbidden()
            ->assertJsonPath('errors.0.detail', 'You do not have permission to access SFTP for this server.');
    }

    /**
     * @dataProvider serverStateDataProvider
     */
    public function testInvalidServerStateReturnsConflictError(string $status)
    {
        $this->server->update(['status' => $status]);

        $this->postJson('/api/remote/sftp/auth', ['username' => $this->getUsername(), 'password' => 'foobar'])
            ->assertStatus(409);
    }

    /**
     * Test that permissions are returned for the user account correctly.
     */
    public function testUserPermissionsAreReturnedCorrectly()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_READ, Permission::ACTION_FILE_SFTP]);

        $user->update(['password' => password_hash('foobar', PASSWORD_DEFAULT)]);

        $this->setAuthorization($server->cluster);

        $data = [
            'username' => $user->username . '.' . $server->uuidShort,
            'password' => 'foobar',
        ];

        $this->postJson('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('permissions', [Permission::ACTION_FILE_READ, Permission::ACTION_FILE_SFTP]);

        $user->update(['root_admin' => true]);

        $this->postJson('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('permissions.0', '*');

        $this->setAuthorization();
        $data['username'] = $user->username . '.' . $this->server->uuidShort;

        $this->post('/api/remote/sftp/auth', $data)
            ->assertOk()
            ->assertJsonPath('permissions.0', '*');

        $user->update(['root_admin' => false]);
        $this->post('/api/remote/sftp/auth', $data)->assertForbidden();
    }

    public function authorizationTypeDataProvider(): array
    {
        return [
            'password auth' => ['password'],
            'public key auth' => ['public_key'],
        ];
    }

    public function serverStateDataProvider(): array
    {
        return [
            'installing' => [Server::STATUS_INSTALLING],
            'suspended' => [Server::STATUS_SUSPENDED],
            'restoring a snapshot' => [Server::STATUS_RESTORING_SNAPSHOT],
        ];
    }

    /**
     * Returns the username for connecting to SFTP.
     */
    protected function getUsername(bool $long = false): string
    {
        return $this->user->username . '.' . ($long ? $this->server->uuid : $this->server->uuidShort);
    }

    /**
     * Sets the authorization header for the rest of the test.
     */
    protected function setAuthorization(Cluster $cluster = null): void
    {
        $cluster = $cluster ?? $this->server->cluster;

        $this->withHeader('Authorization', 'Bearer ' . $cluster->daemon_token_id . '.' . decrypt($cluster->daemon_token));
    }
}
