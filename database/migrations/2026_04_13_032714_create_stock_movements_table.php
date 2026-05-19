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
      Schema::create('stock_movements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('batch_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Auditor
        $table->enum('type', ['in', 'out', 'adjustment']); // Unified logic
        $table->integer('quantity');
        $table->string('reason')->nullable(); // e.g., "Sale", "New Delivery", "Damaged"
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
