<?php

namespace Kubectyl\Services\Rockets\Sharing;

use Carbon\Carbon;
use Kubectyl\Models\Rocket;
use Illuminate\Support\Collection;
use Kubectyl\Models\RocketVariable;
use Kubectyl\Contracts\Repository\RocketRepositoryInterface;

class RocketExporterService
{
    /**
     * RocketExporterService constructor.
     */
    public function __construct(protected RocketRepositoryInterface $repository)
    {
    }

    /**
     * Return a JSON representation of an rocket and its variables.
     *
     * @throws \Kubectyl\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(int $rocket): string
    {
        $rocket = $this->repository->getWithExportAttributes($rocket);

        $struct = [
            '_comment' => 'DO NOT EDIT: FILE GENERATED AUTOMATICALLY BY KUBECTYL PANEL - KUBECTYL.ORG',
            'meta' => [
                'version' => Rocket::EXPORT_VERSION,
                'update_url' => $rocket->update_url,
            ],
            'exported_at' => Carbon::now()->toAtomString(),
            'name' => $rocket->name,
            'author' => $rocket->author,
            'description' => $rocket->description,
            'features' => $rocket->features,
            'docker_images' => $rocket->docker_images,
            'file_denylist' => Collection::make($rocket->inherit_file_denylist)->filter(function ($value) {
                return !empty($value);
            }),
            'startup' => $rocket->startup,
            'config' => [
                'files' => $rocket->inherit_config_files,
                'startup' => $rocket->inherit_config_startup,
                'logs' => $rocket->inherit_config_logs,
                'stop' => $rocket->inherit_config_stop,
            ],
            'scripts' => [
                'installation' => [
                    'script' => $rocket->copy_script_install,
                    'container' => $rocket->copy_script_container,
                    'entrypoint' => $rocket->copy_script_entry,
                ],
            ],
            'variables' => $rocket->variables->transform(function (RocketVariable $item) {
                return Collection::make($item->toArray())
                    ->except(['id', 'rocket_id', 'created_at', 'updated_at'])
                    ->merge(['field_type' => 'text'])
                    ->toArray();
            }),
        ];

        return json_encode($struct, JSON_PRETTY_PRINT);
    }
}
