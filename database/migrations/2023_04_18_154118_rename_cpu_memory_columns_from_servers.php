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
        Schema::table('servers', function (Blueprint $table) {
            $table->integer('memory_limit')->unsigned()->after('memory');
            $table->integer('cpu_limit')->unsigned()->after('cpu');

            $table->renameColumn('memory', 'memory_request');
            $table->renameColumn('cpu', 'cpu_request');
        });

        Schema::table('clusters', function (Blueprint $table) {
            $table->dropColumn('memory');
            $table->dropColumn('disk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('memory_limit');
            $table->dropColumn('cpu_limit');

            $table->renameColumn('memory_request', 'memory');
            $table->renameColumn('cpu_request', 'cpu');
        });
    }
};
