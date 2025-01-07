<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('server_transfers', function (Blueprint $table) {
            $table->renameColumn('old_node', 'old_cluster');
            $table->renameColumn('new_node', 'new_cluster');
        });

        // Schema::table('allocations', function (Blueprint $table) {
        //     $table->dropForeign('allocations_node_foreign');
        //     $table->dropForeign('allocations_assigned_to_foreign');
        //     $table->dropIndex('allocations_node_foreign');
        //     $table->dropIndex('allocations_assigned_to_foreign');

        //     $table->renameColumn('node', 'node_id');
        //     $table->renameColumn('assigned_to', 'server_id');
        //     $table->foreign('node_id')->references('id')->on('nodes');
        //     $table->foreign('server_id')->references('id')->on('servers');
        // });

        Schema::table('backups', function (Blueprint $table) {
            $table->string('snapcontent')->nullable();
        });

        Schema::rename('backups', 'snapshots');

        Schema::table('api_keys', function (Blueprint $table) {
            $table->renameColumn('r_nests', 'r_launchpads');
            $table->renameColumn('r_eggs', 'r_rockets');
        });

        Schema::rename('nests', 'launchpads');
        Schema::rename('eggs', 'rockets');

        Schema::table('rockets', function (Blueprint $table) {
            $table->json('node_selectors')->after('docker_images')->nullable();
        });

        Schema::rename('egg_variables', 'rocket_variables');
        Schema::rename('egg_mount', 'rocket_mount');

        Schema::table('rocket_variables', function (Blueprint $table) {
            $table->renameColumn('egg_id', 'rocket_id');
        });

        Schema::table('rocket_mount', function (Blueprint $table) {
            $table->renameColumn('egg_id', 'rocket_id');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->renameColumn('nest_id', 'launchpad_id');
            $table->renameColumn('egg_id', 'rocket_id');
            $table->renameColumn('backup_limit', 'snapshot_limit');
            $table->json('node_selectors')->after('egg_id')->nullable();
        });

        Schema::table('rockets', function (Blueprint $table) {
            $table->renameColumn('nest_id', 'launchpad_id');
        });

        Schema::table('clusters', function (Blueprint $table) {
            $table->string('metrics')->after('ca_file');
            $table->string('prometheus_address')->after('metrics')->nullable();
            $table->string('external_traffic_policy')->before('image_pull_policy');
            $table->string('snapshot_class')->after('ns');

            $table->string('sftp_image')->before('daemonSFTP');
            $table->renameColumn('daemonSFTP', 'sftp_port');
        });

        Schema::table('api_keys', function (Blueprint $table) {
            $table->renameColumn('r_nodes', 'r_clusters');
        });

        Schema::table('database_hosts', function (Blueprint $table) {
            $table->renameColumn('node_id', 'cluster_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clusters', function (Blueprint $table) {
        });
    }
};
