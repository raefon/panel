<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Settings;

use Kubectyl\Models\Permission;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;

class ReinstallServerRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SETTINGS_REINSTALL;
    }

    /**
     * The rules to apply when validating this request.
     */
    public function rules(): array
    {
        return [
            'delete_files' => 'required|boolean',
        ];
    }
}
