<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_professionals', function (Blueprint $table) {
            $table->id();
            $table->string('professional_id')->nullable();
            $table->string('job_listing_id')->nullable();
            $table->enum('status', ['On the job', 'called in sick', 'Did not show up', 'Not available'])->nullable()->default('On the job');
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
        Schema::dropIfExists('job_professionals');
    }
};
