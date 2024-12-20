<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('professor_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_section_id')->constrained()->onDelete('cascade');
            $table->unique(['professor_id', 'course_section_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('professor_section');
    }
};
