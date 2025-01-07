<?php

namespace Kubectyl\Transformers\Api\Application;

use Illuminate\Support\Arr;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\Server;
use Kubectyl\Models\Launchpad;
use League\Fractal\Resource\Item;
use Kubectyl\Models\RocketVariable;
use Kubectyl\Services\Acl\Api\AdminAcl;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;

class RocketTransformer extends BaseTransformer
{
    /**
     * Relationships that can be loaded onto this transformation.
     */
    protected array $availableIncludes = [
        'launchpad',
        'servers',
        'config',
        'script',
        'variables',
    ];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Rocket::RESOURCE_NAME;
    }

    /**
     * Transform an Rocket model into a representation that can be consumed by
     * the application api.
     *
     * @throws \JsonException
     */
    public function transform(Rocket $model): array
    {
        $files = json_decode($model->config_files, true, 512, JSON_THROW_ON_ERROR);
        if (empty($files)) {
            $files = new \stdClass();
        }

        return [
            'id' => $model->id,
            'uuid' => $model->uuid,
            'name' => $model->name,
            'launchpad' => $model->launchpad_id,
            'author' => $model->author,
            'description' => $model->description,
            // "docker_image" is deprecated, but left here to avoid breaking too many things at once
            // in external software. We'll remove it down the road once things have gotten the chance
            // to upgrade to using "docker_images".
            'docker_image' => count($model->docker_images) > 0 ? Arr::first($model->docker_images) : '',
            'docker_images' => $model->docker_images,
            'config' => [
                'files' => $files,
                'startup' => json_decode($model->config_startup, true),
                'stop' => $model->config_stop,
                'logs' => json_decode($model->config_logs, true),
                'file_denylist' => $model->file_denylist,
                'extends' => $model->config_from,
            ],
            'startup' => $model->startup,
            'script' => [
                'privileged' => $model->script_is_privileged,
                'install' => $model->script_install,
                'entry' => $model->script_entry,
                'container' => $model->script_container,
                'extends' => $model->copy_script_from,
            ],
            $model->getCreatedAtColumn() => $this->formatTimestamp($model->created_at),
            $model->getUpdatedAtColumn() => $this->formatTimestamp($model->updated_at),
        ];
    }

    /**
     * Include the Launchpad relationship for the given Rocket in the transformation.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeLaunchpad(Rocket $model): Item|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_LAUNCHPADS)) {
            return $this->null();
        }

        $model->loadMissing('launchpad');

        return $this->item($model->getRelation('launchpad'), $this->makeTransformer(LaunchpadTransformer::class), Launchpad::RESOURCE_NAME);
    }

    /**
     * Include the Servers relationship for the given Rocket in the transformation.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeServers(Rocket $model): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_SERVERS)) {
            return $this->null();
        }

        $model->loadMissing('servers');

        return $this->collection($model->getRelation('servers'), $this->makeTransformer(ServerTransformer::class), Server::RESOURCE_NAME);
    }

    /**
     * Include more detailed information about the configuration if this Rocket is
     * extending another.
     */
    public function includeConfig(Rocket $model): Item|NullResource
    {
        if (is_null($model->config_from)) {
            return $this->null();
        }

        $model->loadMissing('configFrom');

        return $this->item($model, function (Rocket $model) {
            return [
                'files' => json_decode($model->inherit_config_files),
                'startup' => json_decode($model->inherit_config_startup),
                'stop' => $model->inherit_config_stop,
                'logs' => json_decode($model->inherit_config_logs),
            ];
        });
    }

    /**
     * Include more detailed information about the script configuration if the
     * Rocket is extending another.
     */
    public function includeScript(Rocket $model): Item|NullResource
    {
        if (is_null($model->copy_script_from)) {
            return $this->null();
        }

        $model->loadMissing('scriptFrom');

        return $this->item($model, function (Rocket $model) {
            return [
                'privileged' => $model->script_is_privileged,
                'install' => $model->copy_script_install,
                'entry' => $model->copy_script_entry,
                'container' => $model->copy_script_container,
            ];
        });
    }

    /**
     * Include the variables that are defined for this Rocket.
     *
     * @throws \Kubectyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeVariables(Rocket $model): Collection|NullResource
    {
        if (!$this->authorize(AdminAcl::RESOURCE_ROCKETS)) {
            return $this->null();
        }

        $model->loadMissing('variables');

        return $this->collection(
            $model->getRelation('variables'),
            $this->makeTransformer(RocketVariableTransformer::class),
            RocketVariable::RESOURCE_NAME
        );
    }
}
