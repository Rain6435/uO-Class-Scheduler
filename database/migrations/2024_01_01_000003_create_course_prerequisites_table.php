<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('courses')->onDelete('cascade');
            $table->string('condition_group')->nullable(); // For OR conditions
            $table->unique(['course_id', 'prerequisite_id', 'condition_group'], 'unique_prerequisite');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_prerequisites');
    }
};
