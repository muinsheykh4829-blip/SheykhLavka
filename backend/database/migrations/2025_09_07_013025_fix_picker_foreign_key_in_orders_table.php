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
            // Удаляем старый внешний ключ
            $table->dropForeign(['picker_id']);
            
            // Создаем новый внешний ключ на таблицу pickers
            $table->foreign('picker_id')->references('id')->on('pickers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем новый внешний ключ
            $table->dropForeign(['picker_id']);
            
            // Восстанавливаем старый внешний ключ на users
            $table->foreign('picker_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
