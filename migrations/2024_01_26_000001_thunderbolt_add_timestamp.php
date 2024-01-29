<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class ThunderboltAddTimestamp extends Migration
{
    private $tableName = 'thunderbolt';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->bigInteger('timestamp')->nullable();
            $table->boolean('connected')->nullable();
            $table->string('switch_uid_key')->default('');
        });
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('timestamp');
            $table->dropColumn('connected');
            $table->dropColumn('switch_uid_key');
        });
    }
}
