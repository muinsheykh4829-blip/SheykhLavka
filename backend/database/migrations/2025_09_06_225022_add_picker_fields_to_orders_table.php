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
            $table->unsignedBigInteger('picker_id')->nullable()->after('user_id');
            $table->timestamp('picking_started_at')->nullable()->after('delivery_time');
            $table->timestamp('picking_completed_at')->nullable()->after('picking_started_at');
            
            $table->foreign('picker_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['picker_id']);
            $table->dropColumn(['picker_id', 'picking_started_at', 'picking_completed_at']);
        });
    }
};
