<?php

namespace Kubectyl\Services\Rockets\Sharing;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Arr;
use Kubectyl\Models\Rocket;
use Kubectyl\Models\Launchpad;
use Illuminate\Http\UploadedFile;
use Kubectyl\Models\RocketVariable;
use Illuminate\Database\ConnectionInterface;
use Kubectyl\Services\Rockets\RocketParserService;

class RocketImporterService
{
    public function __construct(protected ConnectionInterface $connection, protected RocketParserService $parser)
    {
    }

    /**
     * Take an uploaded JSON file and parse it into a new rocket.
     *
     * @throws \Kubectyl\Exceptions\Service\InvalidFileUploadException|\Throwable
     */
    public function handle(UploadedFile $file, int $launchpad): Rocket
    {
        $parsed = $this->parser->handle($file);

        /** @var \Kubectyl\Models\Launchpad $launchpad */
        $launchpad = Launchpad::query()->with('rockets', 'rockets.variables')->findOrFail($launchpad);

        return $this->connection->transaction(function () use ($launchpad, $parsed) {
            $rocket = (new Rocket())->forceFill([
                'uuid' => Uuid::uuid4()->toString(),
                'launchpad_id' => $launchpad->id,
                'author' => Arr::get($parsed, 'author'),
                'copy_script_from' => null,
            ]);

            $rocket = $this->parser->fillFromParsed($rocket, $parsed);
            $rocket->save();

            foreach ($parsed['variables'] ?? [] as $variable) {
                RocketVariable::query()->forceCreate(array_merge($variable, ['rocket_id' => $rocket->id]));
            }

            return $rocket;
        });
    }
}
