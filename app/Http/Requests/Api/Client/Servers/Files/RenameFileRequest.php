<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Files;

use Kubectyl\Models\Permission;
use Kubectyl\Contracts\Http\ClientPermissionsRequest;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;

class RenameFileRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    /**
     * The permission the user is required to have in order to perform this
     * request action.
     */
    public function permission(): string
    {
        return Permission::ACTION_FILE_UPDATE;
    }

    public function rules(): array
    {
        return [
            'root' => 'required|nullable|string',
            'files' => 'required|array',
            'files.*' => 'array',
            'files.*.to' => 'required|string',
            'files.*.from' => 'required|string',
        ];
    }
}
