<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Changes foreign key constraints to:
     * - RESTRICT on DELETE (prevent orphaning)
     * - CASCADE on UPDATE (propagate name changes)
     */
    public function up(): void
    {
        // Fix products table constraints
        Schema::table('products', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['category_id']);
            $table->dropForeign(['unit_id']);
            
            // Recreate with RESTRICT on delete, CASCADE on update
            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
                  
            $table->foreign('unit_id')
                  ->references('id')->on('units')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // Fix stock_movements table - prevent user deletion if they have movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // Update supplier foreign key if it exists
        try {
            Schema::table('batches', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
            });
            
            Schema::table('batches', function (Blueprint $table) {
                $table->foreign('supplier_id')
                      ->references('id')->on('suppliers')
                      ->onUpdate('cascade')
                      ->onDelete('restrict');
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original CASCADE constraints
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['unit_id']);
            
            $table->foreign('category_id')->constrained()->onDelete('cascade');
            $table->foreign('unit_id')->constrained()->onDelete('cascade');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->constrained()->onDelete('cascade');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
    }
};
