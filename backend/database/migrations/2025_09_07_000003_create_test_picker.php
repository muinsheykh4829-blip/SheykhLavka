<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Picker;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up()
    {
        // Проверяем, существует ли таблица pickers
        if (!Schema::hasTable('pickers')) {
            echo "Таблица pickers не существует. Пропускаем создание тестовых данных.\n";
            return;
        }
        
        // Сначала удаляем, если существует
        Picker::where('login', 'picker1')->delete();
        
        // Создаем тестового сборщика с прямым хешированием
        $picker = new Picker();
        $picker->login = 'picker1';
        $picker->password = '123456'; // Сеттер сам захеширует
        $picker->name = 'Тестовый Сборщик';
        $picker->phone = '+998901234567';
        $picker->is_active = true;
        $picker->save();
        
        echo "Создан тестовый сборщик: picker1 / 123456\n";
    }

    public function down()
    {
        Picker::where('login', 'picker1')->delete();
    }
};
