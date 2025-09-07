<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->unique();
            $table->string('provider');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->integer('cpu_cores')->unsigned();
            $table->integer('ram_mb')->unsigned();
            $table->integer('storage_gb')->unsigned();
            $table->timestamps();

            // Add a unique constraint on 'name' per 'provider'
            $table->unique(['name', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servers');
    }
}

