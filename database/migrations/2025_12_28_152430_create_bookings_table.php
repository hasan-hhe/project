<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_price', 12, 2);
            $table->string('cancel_reason')->nullable();
            $table->enum('status', ['PENDING', 'CONFIRMED', 'CANCLED', 'COMPLETED'])->default('PENDING');
            $table->timestampsTz();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
