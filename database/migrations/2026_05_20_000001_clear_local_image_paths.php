<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clear local image paths that don't exist on the production server.
     * Images stored as relative paths (e.g. "products/file.jpg") are local-only.
     * Cloudinary images start with "https://" and are kept as-is.
     */
    public function up(): void
    {
        // Set image_path to null for any path that is NOT a full URL
        // This clears old local paths while preserving any Cloudinary URLs
        DB::table('products')
            ->whereNotNull('image_path')
            ->where('image_path', 'not like', 'http%')
            ->update(['image_path' => null]);
    }

    public function down(): void
    {
        // Cannot restore deleted paths
    }
};
