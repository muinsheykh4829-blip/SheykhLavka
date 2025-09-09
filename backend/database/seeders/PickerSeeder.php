<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Picker;

class PickerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем тестовых сборщиков
        $pickers = [
            [
                'login' => 'ahmed',
                'password' => '123456',
                'name' => 'Ахмед Алиев',
                'phone' => '+992937123456',
                'is_active' => true
            ],
            [
                'login' => 'fatima',
                'password' => '123456',
                'name' => 'Фатима Рахимова',
                'phone' => '+992907234567',
                'is_active' => true
            ],
            [
                'login' => 'daler',
                'password' => '123456',
                'name' => 'Далер Салимов',
                'phone' => '+992917345678',
                'is_active' => true
            ]
        ];

        foreach ($pickers as $pickerData) {
            Picker::firstOrCreate(
                ['login' => $pickerData['login']],
                $pickerData
            );
        }
    }
}
