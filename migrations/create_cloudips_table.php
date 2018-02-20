<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCloudipsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cloudips', function (Blueprint $table) {
            $table->string('cidr_ip', 128)->primary();
            $table->string('source', 45);
            $table->string('region', 45);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cloudips');
    }
}
