<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Показать список пользователей
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Поиск по имени, email или телефону
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по статусу верификации
        if ($request->filled('verified')) {
            if ($request->verified === 'yes') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->verified === 'no') {
                $query->whereNull('email_verified_at');
            }
        }
        
        // Сортировка
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $users = $query->orderBy($sortBy, $sortDirection)->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Показать детали пользователя
     */
    public function show(User $user)
    {
        // Загружаем заказы пользователя
        $orders = $user->orders()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Статистика пользователя
        $stats = [
            'total_orders' => $user->orders()->count(),
            'completed_orders' => $user->orders()->where('status', 'completed')->count(),
            'total_spent' => $user->orders()->where('status', 'completed')->sum('total'),
            'avg_order_value' => $user->orders()->where('status', 'completed')->avg('total'),
            'last_order_date' => $user->orders()->latest()->first()?->created_at,
        ];
        
        return view('admin.users.show', compact('user', 'orders', 'stats'));
    }

    /**
     * Форма редактирования пользователя
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Обновить пользователя
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.users.index')
                        ->with('success', 'Пользователь успешно обновлен');
    }

    /**
     * Заблокировать/разблокировать пользователя
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'разблокирован' : 'заблокирован';
        
        return redirect()->back()
                        ->with('success', "Пользователь {$status}");
    }

    /**
     * Удалить пользователя
     */
    public function destroy(User $user)
    {
        // Проверяем, есть ли у пользователя заказы
        if ($user->orders()->exists()) {
            return redirect()->back()
                           ->with('error', 'Нельзя удалить пользователя с существующими заказами');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'Пользователь удален');
    }
}
