<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class ThunderboltDropUnique extends Migration
{
    private $tableName = 'thunderbolt';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
        });
        
        // Force reload of thunderbolt data
        $capsule::unprepared("UPDATE hash SET hash = 'x' WHERE name = '$this->tableName'");
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->unique('serial_number');
        });
    }
}
