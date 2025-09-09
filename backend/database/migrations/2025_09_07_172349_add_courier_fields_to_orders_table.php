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
            $table->unsignedBigInteger('courier_id')->nullable()->after('picker_id');
            $table->unsignedBigInteger('delivered_by')->nullable()->after('courier_id');
            $table->timestamp('delivered_at')->nullable()->after('delivered_by');
            
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('set null');
            $table->foreign('delivered_by')->references('id')->on('couriers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropForeign(['delivered_by']);
            $table->dropColumn(['courier_id', 'delivered_by', 'delivered_at']);
        });
    }
};
