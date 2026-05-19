<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;

class TestProductSeeder extends Seeder
{
    /**
     * Create test products for archive testing
     * These products have NO batches, so they can be easily archived
     */
    public function run(): void
    {
        $this->command->info('Creating test products for archive testing...');

        // Get first category and unit
        $category = Category::first();
        $unit = Unit::first();

        if (!$category || !$unit) {
            $this->command->error('No categories or units found. Please seed them first.');
            return;
        }

        // Create 5 test products with no batches
        $testProducts = [
            ['name' => 'Test Product 1 - No Stock', 'sku' => 'TEST-001'],
            ['name' => 'Test Product 2 - No Stock', 'sku' => 'TEST-002'],
            ['name' => 'Test Product 3 - No Stock', 'sku' => 'TEST-003'],
            ['name' => 'Discontinued Item A', 'sku' => 'DISC-001'],
            ['name' => 'Discontinued Item B', 'sku' => 'DISC-002'],
        ];

        foreach ($testProducts as $productData) {
            Product::create([
                'name' => $productData['name'],
                'sku' => $productData['sku'],
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'min_stock_level' => 10,
            ]);
        }

        $this->command->info('✓ Created 5 test products with NO batches');
        $this->command->info('✓ These can be archived immediately for testing');
        $this->command->info('✓ Go to Product Management and try deleting them!');
    }
}
