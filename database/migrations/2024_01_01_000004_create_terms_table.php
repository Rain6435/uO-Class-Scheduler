<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->enum('term', ['fall', 'winter', 'summer']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unique(['year', 'term']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('terms');
    }
};
