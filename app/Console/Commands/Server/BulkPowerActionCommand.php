<?php

namespace Kubectyl\Console\Commands\Server;

use Kubectyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Factory as ValidatorFactory;
use Kubectyl\Repositories\Kuber\DaemonPowerRepository;
use Kubectyl\Exceptions\Http\Connection\DaemonConnectionException;

class BulkPowerActionCommand extends Command
{
    protected $signature = 'p:server:bulk-power
                            {action : The action to perform (start, stop, restart, kill)}
                            {--servers= : A comma separated list of servers.}
                            {--clusters= : A comma separated list of clusters.}';

    protected $description = 'Perform bulk power management on large groupings of servers or clusters at once.';

    /**
     * BulkPowerActionCommand constructor.
     */
    public function __construct(private DaemonPowerRepository $powerRepository, private ValidatorFactory $validator)
    {
        parent::__construct();
    }

    /**
     * Handle the bulk power request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle()
    {
        $action = $this->argument('action');
        $clusters = empty($this->option('clusters')) ? [] : explode(',', $this->option('clusters'));
        $servers = empty($this->option('servers')) ? [] : explode(',', $this->option('servers'));

        $validator = $this->validator->make([
            'action' => $action,
            'clusters' => $clusters,
            'servers' => $servers,
        ], [
            'action' => 'string|in:start,stop,kill,restart',
            'clusters' => 'array',
            'clusters.*' => 'integer|min:1',
            'servers' => 'array',
            'servers.*' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            foreach ($validator->getMessageBag()->all() as $message) {
                $this->output->error($message);
            }

            throw new ValidationException($validator);
        }

        $count = $this->getQueryBuilder($servers, $clusters)->count();
        if (!$this->confirm(trans('command/messages.server.power.confirm', ['action' => $action, 'count' => $count])) && $this->input->isInteractive()) {
            return;
        }

        $bar = $this->output->createProgressBar($count);
        $powerRepository = $this->powerRepository;
        $this->getQueryBuilder($servers, $clusters)->each(function (Server $server) use ($action, $powerRepository, &$bar) {
            $bar->clear();

            try {
                $powerRepository->setServer($server)->send($action);
            } catch (DaemonConnectionException $exception) {
                $this->output->error(trans('command/messages.server.power.action_failed', [
                    'name' => $server->name,
                    'id' => $server->id,
                    'cluster' => $server->cluster->name,
                    'message' => $exception->getMessage(),
                ]));
            }

            $bar->advance();
            $bar->display();
        });

        $this->line('');
    }

    /**
     * Returns the query builder instance that will return the servers that should be affected.
     */
    protected function getQueryBuilder(array $servers, array $clusters): Builder
    {
        $instance = Server::query()->whereNull('status');

        if (!empty($clusters) && !empty($servers)) {
            $instance->whereIn('id', $servers)->orWhereIn('cluster_id', $clusters);
        } elseif (empty($clusters) && !empty($servers)) {
            $instance->whereIn('id', $servers);
        } elseif (!empty($clusters) && empty($servers)) {
            $instance->whereIn('cluster_id', $clusters);
        }

        return $instance->with('cluster');
    }
}
