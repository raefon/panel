<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Settings;

use Kubectyl\Models\Server;
use Webmozart\Assert\Assert;
use Illuminate\Validation\Rule;
use Kubectyl\Models\Permission;
use Kubectyl\Contracts\Http\ClientPermissionsRequest;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;

class SetDockerImageRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_STARTUP_DOCKER_IMAGE;
    }

    public function rules(): array
    {
        /** @var \Kubectyl\Models\Server $server */
        $server = $this->route()->parameter('server');

        Assert::isInstanceOf($server, Server::class);

        return [
            'docker_image' => ['required', 'string', Rule::in(array_values($server->rocket->docker_images))],
        ];
    }
}
