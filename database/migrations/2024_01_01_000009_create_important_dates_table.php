<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('important_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('subcategory')->nullable();
            $table->string('description');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('important_dates');
    }
};
