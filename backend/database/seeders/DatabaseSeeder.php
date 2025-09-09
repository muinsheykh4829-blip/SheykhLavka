<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Заполняем базу данных тестовыми данными для продуктового магазина
        $this->call([
            // CategorySeeder::class,  // ОТКЛЮЧЕНО - категории добавляем через админ панель
            // ProductSeeder::class,   // ОТКЛЮЧЕНО - продукты добавляем через админ панель
            BannerSeeder::class,
        ]);

        // Создаем тестового пользователя
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
