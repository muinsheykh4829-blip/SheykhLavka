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
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->enum('product_type', ['piece', 'weight', 'package'])->default('piece')->after('unit');
            }
            if (!Schema::hasColumn('products', 'stock_quantity_current')) {
                $table->decimal('stock_quantity_current', 10, 3)->default(0)->after('stock_quantity');
            }
            if (!Schema::hasColumn('products', 'stock_quantity_reserved')) {
                $table->decimal('stock_quantity_reserved', 10, 3)->default(0)->after('stock_quantity_current');
            }
            if (!Schema::hasColumn('products', 'stock_quantity_minimum')) {
                $table->decimal('stock_quantity_minimum', 10, 3)->default(0)->after('stock_quantity_reserved');
            }
            if (!Schema::hasColumn('products', 'stock_unit')) {
                $table->string('stock_unit', 20)->default('шт')->after('stock_quantity_minimum');
            }
            if (!Schema::hasColumn('products', 'conversion_factor')) {
                $table->decimal('conversion_factor', 8, 3)->default(1)->after('stock_unit');
            }
            if (!Schema::hasColumn('products', 'auto_deactivate_on_zero')) {
                $table->boolean('auto_deactivate_on_zero')->default(true)->after('conversion_factor');
            }
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
            if (Schema::hasColumn('products', 'product_type')) $table->dropColumn('product_type');
            if (Schema::hasColumn('products', 'stock_quantity_current')) $table->dropColumn('stock_quantity_current');
            if (Schema::hasColumn('products', 'stock_quantity_reserved')) $table->dropColumn('stock_quantity_reserved');
            if (Schema::hasColumn('products', 'stock_quantity_minimum')) $table->dropColumn('stock_quantity_minimum');
            if (Schema::hasColumn('products', 'stock_unit')) $table->dropColumn('stock_unit');
            if (Schema::hasColumn('products', 'conversion_factor')) $table->dropColumn('conversion_factor');
            if (Schema::hasColumn('products', 'auto_deactivate_on_zero')) $table->dropColumn('auto_deactivate_on_zero');
            if (Schema::hasColumn('products', 'stock_updated_at')) $table->dropColumn('stock_updated_at');
        });
    }
};
