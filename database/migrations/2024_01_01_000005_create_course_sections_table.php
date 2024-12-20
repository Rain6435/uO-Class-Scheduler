<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->string('section_code'); // e.g., "A", "B", "C"
            $table->string('type'); // "LEC", "LAB", "TUT"
            $table->string('status')->nullable(); // "OPEN", "CLOSED"
            $table->text('notes')->nullable();
            $table->unique(['course_id', 'term_id', 'section_code', 'type']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_sections');
    }
};
