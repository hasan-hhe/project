<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_change', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requested_by_user_id');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->date('new_start_date');
            $table->date('new_end_date');
            $table->enum('status', ['PENDING', 'CONFIRMED', 'CANCLED', 'COMPLETED'])->default('PENDING');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_change');
    }
};
