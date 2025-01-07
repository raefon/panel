<?php

namespace Kubectyl\Console;

use Ramsey\Uuid\Uuid;
use Kubectyl\Models\ActivityLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;
use Kubectyl\Repositories\Eloquent\SettingsRepository;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Kubectyl\Services\Telemetry\TelemetryCollectionService;
use Kubectyl\Console\Commands\Schedule\ProcessRunnableCommand;
use Kubectyl\Console\Commands\Maintenance\PruneOrphanedSnapshotsCommand;
use Kubectyl\Console\Commands\Maintenance\CleanServiceSnapshotFilesCommand;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Execute scheduled commands for servers every minute, as if there was a normal cron running.
        $schedule->command(ProcessRunnableCommand::class)->everyMinute()->withoutOverlapping();
        $schedule->command(CleanServiceSnapshotFilesCommand::class)->daily();

        if (config('snapshots.prune_age')) {
            // Every 30 minutes, run the snapshot pruning command so that any abandoned snapshots can be deleted.
            $schedule->command(PruneOrphanedSnapshotsCommand::class)->everyThirtyMinutes();
        }

        if (config('activity.prune_days')) {
            $schedule->command(PruneCommand::class, ['--model' => [ActivityLog::class]])->daily();
        }

        if (config('kubectyl.telemetry.enabled')) {
            $this->registerTelemetry($schedule);
        }
    }

    /**
     * I wonder what this does.
     *
     * @throws \Kubectyl\Exceptions\Model\DataValidationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerTelemetry(Schedule $schedule): void
    {
        $settingsRepository = app()->make(SettingsRepository::class);

        $uuid = $settingsRepository->get('app:telemetry:uuid');
        if (is_null($uuid)) {
            $uuid = Uuid::uuid4()->toString();
            $settingsRepository->set('app:telemetry:uuid', $uuid);
        }

        // Calculate a fixed time to run the data push at, this will be the same time every day.
        $time = hexdec(str_replace('-', '', substr($uuid, 27))) % 1440;
        $hour = floor($time / 60);
        $minute = $time % 60;

        // Run the telemetry collector.
        $schedule->call(app()->make(TelemetryCollectionService::class))->description('Collect Telemetry')->dailyAt("$hour:$minute");
    }
}
