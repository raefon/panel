<?php

namespace Database\Seeders;

use Kubectyl\Models\Rocket;
use Kubectyl\Models\Launchpad;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Kubectyl\Services\Rockets\Sharing\RocketImporterService;
use Kubectyl\Services\Rockets\Sharing\RocketUpdateImporterService;

class RocketSeeder extends Seeder
{
    protected RocketImporterService $importerService;

    protected RocketUpdateImporterService $updateImporterService;

    /**
     * @var string[]
     */
    public static array $import = [
        'Minecraft',
        'Source Engine',
        'Voice Servers',
        'Rust',
    ];

    /**
     * RocketSeeder constructor.
     */
    public function __construct(
        RocketImporterService $importerService,
        RocketUpdateImporterService $updateImporterService
    ) {
        $this->importerService = $importerService;
        $this->updateImporterService = $updateImporterService;
    }

    /**
     * Run the rocket seeder.
     */
    public function run()
    {
        foreach (static::$import as $launchpad) {
            /* @noinspection PhpParamsInspection */
            $this->parseRocketFiles(
                Launchpad::query()->where('author', 'support@kubectyl.org')->where('name', $launchpad)->firstOrFail()
            );
        }
    }

    /**
     * Loop through the list of rocket files and import them.
     */
    protected function parseRocketFiles(Launchpad $launchpad)
    {
        $files = new \DirectoryIterator(database_path('Seeders/rockets/' . kebab_case($launchpad->name)));

        $this->command->alert('Updating Rockets for Launchpad: ' . $launchpad->name);
        /** @var \DirectoryIterator $file */
        foreach ($files as $file) {
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $decoded = json_decode(file_get_contents($file->getRealPath()), true, 512, JSON_THROW_ON_ERROR);
            $file = new UploadedFile($file->getPathname(), $file->getFilename(), 'application/json');

            $rocket = $launchpad->rockets()
                ->where('author', $decoded['author'])
                ->where('name', $decoded['name'])
                ->first();

            if ($rocket instanceof Rocket) {
                $this->updateImporterService->handle($rocket, $file);
                $this->command->info('Updated ' . $decoded['name']);
            } else {
                $this->importerService->handle($file, $launchpad->id);
                $this->command->comment('Created ' . $decoded['name']);
            }
        }

        $this->command->line('');
    }
}
