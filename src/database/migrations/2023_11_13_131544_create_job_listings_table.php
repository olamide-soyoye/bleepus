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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->nullable();
            $table->string('job_title')->nullable();
            $table->text('job_description')->nullable();
            $table->string('address')->nullable();
            $table->string('availability')->nullable();
            $table->integer('job_type_id')->nullable();
            $table->string('wage')->nullable();
            $table->string('duration')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('qualifications')->nullable();
            $table->enum('urgency', ['now', 'tomorrow', '7 days time'])->nullable()->default('now');
            $table->string('tasks')->nullable();
            $table->enum('status', ['Published', 'Occupied', 'Completed'])->nullable()->default('Published');
            $table->enum('payment_status', ['Paid', 'Unpaid'])->nullable()->default('Unpaid');
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
        Schema::dropIfExists('job_listings');
    }
};
