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
        Schema::table('products', function (Blueprint $table) {
            // Тип товара: piece (штучный), weight (весовой), package (упаковочный)
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->enum('product_type', ['piece', 'weight', 'package'])->default('piece')->after('unit');
            }
            
            // Складские остатки
            if (!Schema::hasColumn('products', 'stock_quantity_current')) {
                $table->decimal('stock_quantity_current', 10, 3)->default(0)->after('stock_quantity');
            }
            if (!Schema::hasColumn('products', 'stock_quantity_reserved')) {
                $table->decimal('stock_quantity_reserved', 10, 3)->default(0)->after('stock_quantity_current');
            }
            if (!Schema::hasColumn('products', 'stock_quantity_minimum')) {
                $table->decimal('stock_quantity_minimum', 10, 3)->default(0)->after('stock_quantity_reserved');
            }
            
            // Единица измерения для складского учета (кг, шт, упак)
            if (!Schema::hasColumn('products', 'stock_unit')) {
                $table->string('stock_unit', 20)->default('шт')->after('stock_quantity_minimum');
            }
            
            // Коэффициент пересчета (если единица продажи отличается от складской)
            if (!Schema::hasColumn('products', 'conversion_factor')) {
                $table->decimal('conversion_factor', 8, 3)->default(1)->after('stock_unit');
            }
            
            // Автодеактивация при нулевом остатке
            if (!Schema::hasColumn('products', 'auto_deactivate_on_zero')) {
                $table->boolean('auto_deactivate_on_zero')->default(true)->after('conversion_factor');
            }
            
            // История последнего обновления склада
            if (!Schema::hasColumn('products', 'stock_updated_at')) {
                $table->timestamp('stock_updated_at')->nullable()->after('auto_deactivate_on_zero');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_type',
                'stock_quantity_current',
                'stock_quantity_reserved', 
                'stock_quantity_minimum',
                'stock_unit',
                'conversion_factor',
                'auto_deactivate_on_zero',
                'stock_updated_at'
            ]);
        });
    }
};
