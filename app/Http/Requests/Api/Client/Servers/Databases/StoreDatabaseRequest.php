<?php

namespace Kubectyl\Http\Requests\Api\Client\Servers\Databases;

use Kubectyl\Models\Server;
use Webmozart\Assert\Assert;
use Kubectyl\Models\Database;
use Illuminate\Validation\Rule;
use Kubectyl\Models\Permission;
use Illuminate\Database\Query\Builder;
use Kubectyl\Contracts\Http\ClientPermissionsRequest;
use Kubectyl\Http\Requests\Api\Client\ClientApiRequest;
use Kubectyl\Services\Databases\DatabaseManagementService;

class StoreDatabaseRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_CREATE;
    }

    public function rules(): array
    {
        $server = $this->route()->parameter('server');

        Assert::isInstanceOf($server, Server::class);

        return [
            'database' => [
                'required',
                'alpha_dash',
                'min:3',
                'max:48',
                // Yes, I am aware that you could have the same database name across two unique hosts. However,
                // I don't really care about that for this validation. We just want to make sure it is unique to
                // the server itself. No need for complexity.
                Rule::unique('databases')->where(function (Builder $query) use ($server) {
                    $query->where('server_id', $server->id)
                        ->where('database', DatabaseManagementService::generateUniqueDatabaseName($this->input('database'), $server->id));
                }),
            ],
            'remote' => Database::getRulesForField('remote'),
        ];
    }

    public function messages(): array
    {
        return [
            'database.unique' => 'The database name you have selected is already in use by this server.',
        ];
    }
}
