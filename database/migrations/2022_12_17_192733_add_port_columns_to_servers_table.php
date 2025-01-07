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
            $table->integer('allocation_id')->nullable()->unsigned()->change();
            $table->string('default_port')->nullable()->after('allocation_id');
            $table->text('additional_ports')->nullable()->after('default_port');
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
            $table->dropIfExists('default_port');
            $table->dropIfExists('additional_ports');
        });
    }
};
