<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('track_stock')->default(true)->after('stock');
            $table->json('variants')->nullable()->after('image');
            $table->json('supplements')->nullable()->after('variants');
            $table->softDeletes();
            
            // Index pour recherche rapide
            $table->index('is_active');
            $table->index(['track_stock', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['track_stock', 'stock']);
            
            $table->dropSoftDeletes();
            $table->dropColumn([
                'track_stock',
                'variants',
                'supplements',
            ]);
        });
    }
};
