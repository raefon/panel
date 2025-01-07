<?php

namespace Kubectyl\Services\Servers;

use Kubectyl\Models\User;
use Illuminate\Support\Arr;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\Server;
use Kubectyl\Models\ServerVariable;
use Kubectyl\Traits\Services\HasUserLevels;
use Illuminate\Database\ConnectionInterface;

class StartupModificationService
{
    use HasUserLevels;

    /**
     * StartupModificationService constructor.
     */
    public function __construct(private ConnectionInterface $connection, private VariableValidatorService $validatorService)
    {
    }

    /**
     * Process startup modification for a server.
     *
     * @throws \Throwable
     */
    public function handle(Server $server, array $data): Server
    {
        return $this->connection->transaction(function () use ($server, $data) {
            if (!empty($data['environment'])) {
                $rocket = $this->isUserLevel(User::USER_LEVEL_ADMIN) ? ($data['rocket_id'] ?? $server->rocket_id) : $server->rocket_id;

                $results = $this->validatorService
                    ->setUserLevel($this->getUserLevel())
                    ->handle($rocket, $data['environment']);

                foreach ($results as $result) {
                    ServerVariable::query()->updateOrCreate(
                        [
                            'server_id' => $server->id,
                            'variable_id' => $result->id,
                        ],
                        ['variable_value' => $result->value ?? '']
                    );
                }
            }

            if ($this->isUserLevel(User::USER_LEVEL_ADMIN)) {
                $this->updateAdministrativeSettings($data, $server);
            }

            // Calling ->refresh() rather than ->fresh() here causes it to return the
            // variables as triplicates for some reason? Not entirely sure, should dig
            // in more to figure it out, but luckily we have a test case covering this
            // specific call so we can be assured we're not breaking it _here_ at least.
            //
            // TODO(dane): this seems like a red-flag for the code powering the relationship
            //  that should be looked into more.
            return $server->fresh();
        });
    }

    /**
     * Update certain administrative settings for a server in the DB.
     */
    protected function updateAdministrativeSettings(array $data, Server &$server): void
    {
        $rocketId = Arr::get($data, 'rocket_id');

        if (is_digit($rocketId) && $server->rocket_id !== (int) $rocketId) {
            /** @var \Kubectyl\Models\Rocket $rocket */
            $rocket = Rocket::query()->findOrFail($data['rocket_id']);

            $server = $server->forceFill([
                'rocket_id' => $rocket->id,
                'launchpad_id' => $rocket->launchpad_id,
            ]);
        }

        $server->fill([
            'startup' => $data['startup'] ?? $server->startup,
            'skip_scripts' => $data['skip_scripts'] ?? isset($data['skip_scripts']),
            'image' => $data['docker_image'] ?? $server->image,
        ])->save();
    }
}
