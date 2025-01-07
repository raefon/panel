<?php

namespace Kubectyl\Services\Rockets\Sharing;

use Kubectyl\Models\Rocket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Kubectyl\Models\RocketVariable;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Services\Rockets\RocketParserService;

class RocketUpdateImporterService
{
    /**
     * RocketUpdateImporterService constructor.
     */
    public function __construct(protected ConnectionInterface $connection, protected RocketParserService $parser)
    {
    }

    /**
     * Update an existing Rocket using an uploaded JSON file.
     *
     * @throws \Kubectyl\Exceptions\Service\InvalidFileUploadException|\Throwable
     */
    public function handle(Rocket $rocket, UploadedFile $file): Rocket
    {
        $parsed = $this->parser->handle($file);

        return $this->connection->transaction(function () use ($rocket, $parsed) {
            $rocket = $this->parser->fillFromParsed($rocket, $parsed);
            $rocket->save();

            // Update existing variables or create new ones.
            foreach ($parsed['variables'] ?? [] as $variable) {
                RocketVariable::unguarded(function () use ($rocket, $variable) {
                    $rocket->variables()->updateOrCreate([
                        'env_variable' => $variable['env_variable'],
                    ], Collection::make($variable)->except('rocket_id', 'env_variable')->toArray());
                });
            }

            $imported = array_map(fn ($value) => $value['env_variable'], $parsed['variables'] ?? []);

            $rocket->variables()->whereNotIn('env_variable', $imported)->delete();

            return $rocket->refresh();
        });
    }
}
