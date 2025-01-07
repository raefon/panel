<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Files;

use Kubectyl\Models\Permission;
use Kubectyl\Contracts\Http\ClientPermissionsRequest;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;

class PullFileRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_CREATE;
    }

    public function rules(): array
    {
        return [
            'url' => 'required|string|url',
            'directory' => 'nullable|string',
            'filename' => 'nullable|string',
            'use_header' => 'boolean',
            'foreground' => 'boolean',
        ];
    }
}
