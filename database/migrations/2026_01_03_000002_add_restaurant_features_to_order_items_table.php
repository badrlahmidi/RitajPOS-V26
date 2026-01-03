<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->after('quantity');
            $table->decimal('subtotal', 10, 2)->after('unit_price');
            $table->json('variants')->nullable()->after('subtotal');
            $table->json('supplements')->nullable()->after('variants');
            $table->text('notes')->nullable()->after('supplements');
            
            // Renommer 'price' en 'unit_price' si la colonne existe
            // Note: Ajustez selon votre schéma actuel
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'unit_price',
                'subtotal',
                'variants',
                'supplements',
                'notes',
            ]);
        });
    }
};
