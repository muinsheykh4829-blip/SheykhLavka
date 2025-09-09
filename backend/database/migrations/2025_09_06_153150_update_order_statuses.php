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
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем старое ограничение статуса и создаем новое
            $table->dropColumn('status');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            // Добавляем новую колонку со статусами
            $table->enum('status', [
                'processing',    // В обработке
                'accepted',      // Принят
                'preparing',     // Собирается
                'ready',         // Собран
                'delivering',    // Курьер в пути
                'delivered',     // Завершен
                'cancelled'      // Отменен
            ])->default('processing')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled'])
                  ->default('pending')->after('user_id');
        });
    }
};
