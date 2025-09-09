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
        Schema::table('banners', function (Blueprint $table) {
            // Добавляем недостающие столбцы
            $table->string('title_ru')->nullable()->after('title');
            $table->text('description')->nullable()->after('subtitle');
            $table->text('description_ru')->nullable()->after('description');
            $table->string('link_url')->nullable()->after('link_id');
            $table->string('target_audience')->default('all')->after('is_active');
            $table->integer('click_count')->default(0)->after('target_audience');
            $table->integer('view_count')->default(0)->after('click_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn([
                'title_ru',
                'description',
                'description_ru', 
                'link_url',
                'target_audience',
                'click_count',
                'view_count'
            ]);
        });
    }
};
