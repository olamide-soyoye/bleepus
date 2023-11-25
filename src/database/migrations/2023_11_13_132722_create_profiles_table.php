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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            // $table->integer('user_id')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('country_abbr')->nullable();
            $table->string('country_code')->nullable();
            $table->string('address')->nullable();
            $table->string('agency_code')->nullable();
            $table->string('about')->nullable();
            $table->string('total_earnings')->nullable();
            $table->string('pending_payment')->nullable();
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
        Schema::dropIfExists('profiles');
    }
};
