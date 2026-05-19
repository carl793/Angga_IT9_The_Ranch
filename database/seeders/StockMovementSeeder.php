<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Supplier;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    /**
     * Seed realistic stock movements over the past 12 months
     * Simulates a working farm inventory with seasonal patterns
     */
    public function run(): void
    {
        // Get all products, users, and suppliers
        $products = Product::all();
        $users = User::all();
        $suppliers = Supplier::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please seed products first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please seed users first.');
            return;
        }

        if ($suppliers->isEmpty()) {
            $this->command->warn('No suppliers found. Creating sample supplier...');
            $suppliers = collect([
                Supplier::create([
                    'name' => 'General Farm Supplies',
                    'contact_person' => 'John Supplier',
                    'phone' => '555-0100'
                ])
            ]);
        }

        $this->command->info('Generating 12 months of stock movement data...');

        // Generate data for the past 12 months
        for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
            $date = Carbon::now()->subMonths($monthsAgo);
            $this->command->info("Processing {$date->format('F Y')}...");

            // Each month, process each product
            foreach ($products as $product) {
                // Randomly decide if this product has activity this month (80% chance)
                if (rand(1, 100) <= 80) {
                    $this->generateMonthlyActivity($product, $date, $users, $suppliers);
                }
            }
        }

        $this->command->info('Stock movement data generated successfully!');
        $this->command->info('Total Batches: ' . Batch::count());
        $this->command->info('Total Movements: ' . StockMovement::count());
    }

    /**
     * Generate realistic activity for a product in a given month
     */
    private function generateMonthlyActivity($product, $date, $users, $suppliers)
    {
        // Number of stock-in events this month (1-3)
        $stockInCount = rand(1, 3);

        for ($i = 0; $i < $stockInCount; $i++) {
            // Random day in the month
            $stockInDate = $date->copy()->addDays(rand(1, 28));

            // Create a batch with stock in
            $quantity = $this->getRealisticQuantity($product);
            $costPrice = $this->getRealisticCostPrice($product);
            $expiryDate = $this->getRealisticExpiryDate($stockInDate, $product);

            // Generate batch code
            $batchDate = $stockInDate->format('Ymd');
            $sequence = Batch::where('batch_code', 'like', "{$product->sku}-{$batchDate}-%")->count() + 1;
            $batchCode = "{$product->sku}-{$batchDate}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            $batch = Batch::create([
                'product_id' => $product->id,
                'supplier_id' => $suppliers->random()->id,
                'batch_code' => $batchCode,
                'initial_quantity' => $quantity,
                'current_quantity' => $quantity,
                'cost_price' => $costPrice,
                'expiry_date' => $expiryDate,
                'created_at' => $stockInDate,
                'updated_at' => $stockInDate,
            ]);

            // Record stock in movement
            StockMovement::create([
                'batch_id' => $batch->id,
                'user_id' => $users->random()->id,
                'type' => 'in',
                'quantity' => $quantity,
                'reason' => null,
                'created_at' => $stockInDate,
                'updated_at' => $stockInDate,
            ]);

            // Simulate stock outs throughout the month (60% chance)
            if (rand(1, 100) <= 60) {
                $this->generateStockOuts($batch, $stockInDate, $date, $users);
            }
        }
    }

    /**
     * Generate realistic stock out events for a batch
     */
    private function generateStockOuts($batch, $startDate, $monthDate, $users)
    {
        $remainingQty = $batch->current_quantity;
        $stockOutCount = rand(1, 4); // 1-4 stock out events

        for ($i = 0; $i < $stockOutCount && $remainingQty > 0; $i++) {
            // Random day after stock in, within the same month
            $daysAfter = rand(1, min(15, $monthDate->daysInMonth - $startDate->day));
            $stockOutDate = $startDate->copy()->addDays($daysAfter);

            // Stock out quantity (10-50% of remaining)
            $maxStockOut = max(1, (int)($remainingQty * 0.5));
            $stockOutQty = rand(1, $maxStockOut);

            // Update batch
            $batch->current_quantity -= $stockOutQty;
            $batch->updated_at = $stockOutDate;
            $batch->save();

            // Record stock out movement
            $reasons = ['Sale', 'Distribution', 'Farm Use', 'Donation', 'Spoilage', 'Damaged'];
            StockMovement::create([
                'batch_id' => $batch->id,
                'user_id' => $users->random()->id,
                'type' => 'out',
                'quantity' => $stockOutQty,
                'reason' => $reasons[array_rand($reasons)],
                'created_at' => $stockOutDate,
                'updated_at' => $stockOutDate,
            ]);

            $remainingQty = $batch->current_quantity;
        }
    }

    /**
     * Get realistic quantity based on product type
     */
    private function getRealisticQuantity($product)
    {
        // Larger quantities for bulk items
        $ranges = [
            'sack' => [50, 200],
            'bag' => [30, 150],
            'kg' => [100, 500],
            'liter' => [50, 300],
            'piece' => [20, 100],
            'box' => [10, 50],
        ];

        $unitName = strtolower($product->unit->name);
        
        foreach ($ranges as $key => $range) {
            if (str_contains($unitName, $key)) {
                return rand($range[0], $range[1]);
            }
        }

        // Default range
        return rand(50, 200);
    }

    /**
     * Get realistic cost price based on product
     */
    private function getRealisticCostPrice($product)
    {
        // Base prices with some variation
        $basePrice = rand(50, 500);
        $variation = rand(-10, 10) / 100; // -10% to +10%
        
        return round($basePrice * (1 + $variation), 2);
    }

    /**
     * Get realistic expiry date based on product type
     */
    private function getRealisticExpiryDate($stockInDate, $product)
    {
        $categoryName = strtolower($product->category->name);
        
        // Different shelf lives for different categories
        if (str_contains($categoryName, 'feed') || str_contains($categoryName, 'grain')) {
            // 3-6 months
            return $stockInDate->copy()->addMonths(rand(3, 6));
        } elseif (str_contains($categoryName, 'fertilizer') || str_contains($categoryName, 'chemical')) {
            // 12-24 months
            return $stockInDate->copy()->addMonths(rand(12, 24));
        } elseif (str_contains($categoryName, 'seed')) {
            // 6-12 months
            return $stockInDate->copy()->addMonths(rand(6, 12));
        } elseif (str_contains($categoryName, 'vegetable') || str_contains($categoryName, 'produce')) {
            // 1-3 months
            return $stockInDate->copy()->addMonths(rand(1, 3));
        } else {
            // Default: 6-12 months
            return $stockInDate->copy()->addMonths(rand(6, 12));
        }
    }
}
