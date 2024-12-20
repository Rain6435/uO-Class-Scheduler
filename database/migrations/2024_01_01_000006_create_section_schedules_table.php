<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('section_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->onDelete('cascade');
            $table->enum('day', ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']);
            $table->time('start_time');
            $table->time('end_time');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('room')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('section_schedules');
    }
};
