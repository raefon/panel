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
        Schema::rename('nodes', 'clusters');
        Schema::rename('mount_node', 'mount_cluster');

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('swap');
            $table->renameColumn('node_id', 'cluster_id');
        });

        Schema::table('allocations', function (Blueprint $table) {
            $table->renameColumn('node_id', 'cluster_id');
        });

        Schema::table('mount_cluster', function (Blueprint $table) {
            $table->renameColumn('node_id', 'cluster_id');
        });
    }
};
