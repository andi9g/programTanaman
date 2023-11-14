<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Apiiot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perangkat', function (Blueprint $table) {
            $table->bigIncrements('idperangkat');
            $table->String("token");
            $table->integer("hari")->nullable();
            $table->integer("jam")->nullable();
            $table->integer("menit")->nullable();
            $table->enum("umur", ["dewasa", "muda", "anak"])->nullable();
            $table->timestamps();
        });

        DB::table('perangkat')->insert([
            "token" => "65520e72b84cc",
            "hari" => 7,
            "jam" => 6,
            "menit" => 1,
        ]);

        Schema::create('sensor', function (Blueprint $table) {
            $table->bigIncrements('idsensor');
            $table->integer("relay1");
            $table->integer("relay2");
            $table->dateTime("waktu");
            $table->String("ket")->nullable();
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->bigIncrements('idlogs');
            $table->double("sensorDigital");
            $table->double("sensorAnalog");
            $table->double("jarakD5");
            $table->double("jarakD7");
            $table->dateTime("waktu");
            $table->String("ket")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
