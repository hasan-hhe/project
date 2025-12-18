<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('owner_id');
            $table->string('title');
            $table->text('description');
            $table->decimal('price');
            $table->integer('rooms_count');
            $table->bigInteger('city_id');
            $table->bigInteger('governorate_id');
            $table->string('address_line');
            $table->decimal('rating');
            $table->boolean('is_active');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
