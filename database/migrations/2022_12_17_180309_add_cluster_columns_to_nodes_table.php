<?php

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Contracts\Encryption\Encrypter;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->longText('host')->after('daemonBase');
            $table->text('bearer_token')->after('host');
            $table->boolean('insecure')->default(false)->after('bearer_token');
            $table->string('service_type')->after('insecure');
            $table->string('storage_class')->after('service_type');
            $table->string('ns')->after('storage_class');
            $table->string('cert_file')->nullable()->after('ns');
            $table->string('key_file')->nullable()->after('cert_file');
            $table->string('ca_file')->nullable()->after('key_file');
            $table->string('dns_policy')->after('ca_file');
            $table->string('image_pull_policy')->after('dns_policy');
            $table->string('metallb_address_pool')->nullable()->after('image_pull_policy');
            $table->boolean('metallb_shared_ip')->after('metallb_address_pool');
        });

        // /** @var \Illuminate\Contracts\Encryption\Encrypter $encrypter */
        $encrypter = Container::getInstance()->make(Encrypter::class);

        foreach (DB::select('SELECT bearer_token FROM nodes') as $datum) {
            DB::update('UPDATE nodes SET bearer_token = ? WHERE id = ?', [
                Uuid::uuid4()->toString(),
                $datum->bearer_token,
                $encrypter->encrypt($datum->bearer_token),
                $datum->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('host');
            $table->dropColumn('bearer_token');
            $table->dropColumn('insecure');
            $table->dropColumn('service_type');
            $table->dropColumn('storage_class');
            $table->dropColumn('ns');
        });
    }
};
