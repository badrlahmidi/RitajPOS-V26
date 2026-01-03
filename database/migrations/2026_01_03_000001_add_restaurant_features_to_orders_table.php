<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('table_number')->nullable()->after('user_id');
            $table->string('status')->default('pending')->after('table_number');
            $table->decimal('subtotal', 10, 2)->default(0)->after('status');
            $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
            $table->decimal('total', 10, 2)->default(0)->after('tax');
            $table->text('notes')->nullable()->after('total');
            $table->text('cancellation_reason')->nullable()->after('notes');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index('status');
            $table->index('table_number');
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['table_number']);
            $table->dropIndex(['created_at', 'status']);
            
            $table->dropSoftDeletes();
            $table->dropColumn([
                'table_number',
                'status',
                'subtotal',
                'tax',
                'total',
                'notes',
                'cancellation_reason',
                'cancelled_at',
            ]);
        });
    }
};
