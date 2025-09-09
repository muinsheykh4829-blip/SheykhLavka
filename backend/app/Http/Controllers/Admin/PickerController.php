<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Picker;
use Illuminate\Http\Request;

class PickerController extends Controller
{
    /**
     * Список всех сборщиков
     */
    public function index()
    {
        $pickers = Picker::orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.pickers.index', compact('pickers'));
    }

    /**
     * Форма создания нового сборщика
     */
    public function create()
    {
        return view('admin.pickers.create');
    }

    /**
     * Сохранение нового сборщика
     */
    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string|unique:pickers,login|max:255',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20'
        ]);

        Picker::create([
            'login' => $request->login,
            'password' => $request->password,
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('admin.pickers.index')
                         ->with('success', 'Сборщик успешно создан');
    }

    /**
     * Отображение данных сборщика
     */
    public function show(Picker $picker)
    {
        $picker->load('orders');
        
        return view('admin.pickers.show', compact('picker'));
    }

    /**
     * Форма редактирования сборщика
     */
    public function edit(Picker $picker)
    {
        return view('admin.pickers.edit', compact('picker'));
    }

    /**
     * Обновление данных сборщика
     */
    public function update(Request $request, Picker $picker)
    {
        $request->validate([
            'login' => 'required|string|max:255|unique:pickers,login,' . $picker->id,
            'password' => 'nullable|string|min:6',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20'
        ]);

        $data = [
            'login' => $request->login,
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => $request->has('is_active') ? 1 : 0
        ];

        // Обновляем пароль только если он указан
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $picker->update($data);

        return redirect()->route('admin.pickers.index')
                         ->with('success', 'Данные сборщика обновлены');
    }

    /**
     * Удаление сборщика
     */
    public function destroy(Picker $picker)
    {
        // Проверяем, есть ли активные заказы у сборщика
        if ($picker->activeOrders()->count() > 0) {
            return redirect()->route('admin.pickers.index')
                           ->with('error', 'Нельзя удалить сборщика с активными заказами');
        }

        $picker->delete();

        return redirect()->route('admin.pickers.index')
                         ->with('success', 'Сборщик удален');
    }

    /**
     * Активация/деактивация сборщика
     */
    public function toggleStatus(Picker $picker)
    {
        $picker->update(['is_active' => !$picker->is_active]);
        
        $status = $picker->is_active ? 'активирован' : 'деактивирован';
        
        return redirect()->back()
                         ->with('success', "Сборщик {$status}");
    }
}
