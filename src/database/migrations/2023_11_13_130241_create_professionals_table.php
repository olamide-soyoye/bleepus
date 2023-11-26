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
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            // $table->integer('user_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('profile_id')->nullable();
            $table->string('max_distance')->nullable();
            $table->string('profession_title')->nullable();
            $table->string('skills')->nullable();
            $table->string('certifications')->nullable();
            $table->string('specialities')->nullable();
            $table->string('years_of_experience')->nullable();
            $table->string('wage')->nullable();
            $table->enum('status', ['Available', 'Occupied', 'Not Available'])->nullable()->default('Available');
            $table->string('ratings')->nullable(); 
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
        Schema::dropIfExists('professionals');
    }
};
