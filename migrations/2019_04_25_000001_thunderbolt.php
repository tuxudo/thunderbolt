<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class Thunderbolt extends Migration
{
    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->create('thunderbolt', function (Blueprint $table) {
            $table->increments('id');
            $table->string('serial_number')->unique();
            $table->string('name')->nullable();
            $table->string('device_serial_number')->nullable();
            $table->string('vendor')->nullable();
            $table->string('current_speed')->nullable();
            $table->text('device_json')->nullable();
            
            $table->index('serial_number');
            $table->index('name');
            $table->index('device_serial_number');
            $table->index('vendor');
            $table->index('current_speed');

        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->dropIfExists('thunderbolt');
    }
}
