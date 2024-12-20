<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('code', 10); // e.g., "1341", "2143"
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('credits');
            $table->json('components')->nullable(); // ["LEC", "LAB", "TUT"]
            $table->unique(['subject_id', 'code']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
