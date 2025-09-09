<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // В MySQL для изменения enum нужно пересоздать столбец с новыми значениями
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'confirmed', 'processing', 'accepted', 'preparing', 'ready', 'delivering', 'delivered', 'completed', 'cancelled') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Возвращаем к старым значениям
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled') DEFAULT 'pending'");
        });
    }
};
