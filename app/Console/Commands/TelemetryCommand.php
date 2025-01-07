<?php

namespace Kubectyl\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\VarDumper\VarDumper;
use Kubectyl\Services\Telemetry\TelemetryCollectionService;

class TelemetryCommand extends Command
{
    protected $description = 'Displays all the data that would be sent to the Kubectyl Telemetry Service if telemetry collection is enabled.';

    protected $signature = 'p:telemetry';

    /**
     * TelemetryCommand constructor.
     */
    public function __construct(private TelemetryCollectionService $telemetryCollectionService)
    {
        parent::__construct();
    }

    /**
     * Handle execution of command.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     */
    public function handle()
    {
        $this->output->info('Collecting telemetry data, this may take a while...');

        VarDumper::dump($this->telemetryCollectionService->collect());
    }
}
