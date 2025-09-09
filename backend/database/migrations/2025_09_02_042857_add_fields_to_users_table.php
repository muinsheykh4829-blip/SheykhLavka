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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->after('email'); // Телефон пользователя
            $table->string('first_name')->nullable()->after('name'); // Имя
            $table->string('last_name')->nullable()->after('first_name'); // Фамилия
            $table->integer('age')->nullable()->after('last_name'); // Возраст
            $table->date('birth_date')->nullable(); // Дата рождения
            $table->enum('gender', ['male', 'female'])->nullable(); // Пол
            $table->string('avatar')->nullable(); // Аватар пользователя
            $table->timestamp('phone_verified_at')->nullable(); // Время верификации телефона
            $table->string('verification_code', 6)->nullable(); // Код подтверждения
            $table->timestamp('verification_code_expires_at')->nullable(); // Время истечения кода
            $table->boolean('is_active')->default(true); // Активен ли пользователь
            $table->timestamp('last_login_at')->nullable(); // Время последнего входа
            
            $table->index(['phone']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'first_name', 'last_name', 'birth_date', 'gender',
                'avatar', 'phone_verified_at', 'verification_code', 
                'verification_code_expires_at', 'is_active', 'last_login_at'
            ]);
        });
    }
};
