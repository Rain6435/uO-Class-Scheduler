<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('saved_schedule_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_section_id')->constrained()->onDelete('cascade');
            $table->string('color')->nullable(); // For UI customization
            $table->json('notes')->nullable();
            $table->timestamps();

            $table->unique(['saved_schedule_id', 'course_section_id'], 'unique_schedule_section');
        });
    }

    public function down()
    {
        Schema::dropIfExists('saved_schedule_sections');
    }
};
