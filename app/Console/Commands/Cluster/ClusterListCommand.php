<?php

namespace Kubectyl\Console\Commands\Cluster;

use Kubectyl\Models\Cluster;
use Illuminate\Console\Command;

class ClusterListCommand extends Command
{
    protected $signature = 'p:cluster:list {--format=text : The output format: "text" or "json". }';

    public function handle(): int
    {
        $clusters = Cluster::query()->with('location')->get()->map(function (Cluster $cluster) {
            return [
                'id' => $cluster->id,
                'uuid' => $cluster->uuid,
                'name' => $cluster->name,
                'location' => $cluster->location->short,
                'host' => $cluster->getConnectionAddress(),
            ];
        });

        if ($this->option('format') === 'json') {
            $this->output->write($clusters->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['ID', 'UUID', 'Name', 'Location', 'Host'], $clusters->toArray());
        }

        $this->output->newLine();

        return 0;
    }
}
