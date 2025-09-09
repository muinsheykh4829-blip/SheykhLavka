<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CourierController extends Controller
{
    /**
     * Отображает список курьеров с статистикой
     */
    public function index()
    {
        $couriers = Courier::withCount([
            'assignedOrders',
            'deliveredOrders',
            'assignedOrders as active_orders_count' => function ($query) {
                $query->whereIn('status', ['delivering', 'in_delivery']);
            }
        ])->get();

        // Общая статистика
        $totalCouriers = $couriers->count();
        $activeCouriers = $couriers->where('is_active', true)->count();
        $totalDeliveries = $couriers->sum('delivered_orders_count');

        return view('admin.couriers.index', compact(
            'couriers', 
            'totalCouriers', 
            'activeCouriers', 
            'totalDeliveries'
        ));
    }

    /**
     * Форма создания нового курьера
     */
    public function create()
    {
        return view('admin.couriers.create');
    }

    /**
     * Сохранение нового курьера
     */
    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|unique:couriers,login',
            'password' => 'required|min:6',
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean'
        ]);

        Courier::create([
            'login' => $request->login,
            'password' => Hash::make($request->password), // Хешируем пароль
            'name' => $request->name,
            'phone' => $request->phone,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_number' => $request->vehicle_number,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.couriers.index')
            ->with('success', 'Курьер успешно создан!');
    }

    /**
     * Детальный просмотр курьера со статистикой
     */
    public function show(Courier $courier)
    {
        // Загружаем статистику курьера
        $courier->loadCount([
            'assignedOrders',
            'deliveredOrders'
        ]);

        // Подробная статистика
        $stats = [
            'total_assigned' => $courier->assignedOrders()->count(),
            'total_delivered' => $courier->deliveredOrders()->count(),
            'active_orders' => $courier->assignedOrders()->whereIn('status', ['delivering', 'in_delivery'])->count(),
            'completed_today' => $courier->deliveredOrders()->whereDate('delivered_at', today())->count(),
            'completed_this_week' => $courier->deliveredOrders()->whereBetween('delivered_at', [
                now()->startOfWeek(), 
                now()->endOfWeek()
            ])->count(),
            'completed_this_month' => $courier->deliveredOrders()->whereMonth('delivered_at', now()->month)->count(),
            'avg_delivery_time' => $this->getAverageDeliveryTime($courier),
            'total_earnings' => $this->calculateEarnings($courier)
        ];

        // Последние заказы
        $recentOrders = $courier->assignedOrders()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Статистика по дням (последние 7 дней)
        $dailyStats = $this->getDailyStats($courier);

        return view('admin.couriers.show', compact('courier', 'stats', 'recentOrders', 'dailyStats'));
    }

    /**
     * Форма редактирования курьера
     */
    public function edit(Courier $courier)
    {
        return view('admin.couriers.edit', compact('courier'));
    }

    /**
     * Обновление данных курьера
     */
    public function update(Request $request, Courier $courier)
    {
        $request->validate([
            'login' => 'required|unique:couriers,login,' . $courier->id,
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean'
        ]);

        $updateData = [
            'login' => $request->login,
            'name' => $request->name,
            'phone' => $request->phone,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_number' => $request->vehicle_number,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active', true)
        ];

        // Обновляем пароль только если он указан
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $updateData['password'] = Hash::make($request->password);
        }

        $courier->update($updateData);

        return redirect()->route('admin.couriers.show', $courier)
            ->with('success', 'Данные курьера обновлены!');
    }

    /**
     * Удаление курьера
     */
    public function destroy(Courier $courier)
    {
        // Проверяем, есть ли активные заказы
        $activeOrders = $courier->assignedOrders()->whereIn('status', ['delivering', 'in_delivery'])->count();
        
        if ($activeOrders > 0) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить курьера с активными заказами!');
        }

        $courier->delete();

        return redirect()->route('admin.couriers.index')
            ->with('success', 'Курьер удален!');
    }

    /**
     * Активация/деактивация курьера
     */
    public function toggleStatus(Courier $courier)
    {
        $courier->update(['is_active' => !$courier->is_active]);

        $status = $courier->is_active ? 'активирован' : 'деактивирован';
        
        return redirect()->back()
            ->with('success', "Курьер {$status}!");
    }

    /**
     * Вычисляет среднее время доставки
     */
    private function getAverageDeliveryTime(Courier $courier)
    {
        $deliveredOrders = $courier->assignedOrders()
            ->where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->get();

        if ($deliveredOrders->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($deliveredOrders as $order) {
            if ($order->delivered_at && $order->created_at) {
                $minutes = $order->created_at->diffInMinutes($order->delivered_at);
                $totalMinutes += $minutes;
                $count++;
            }
        }

        return $count > 0 ? round($totalMinutes / $count) : 0;
    }

    /**
     * Рассчитывает заработок курьера (условно)
     */
    private function calculateEarnings(Courier $courier)
    {
        // Условно: 500 сом за доставку
        return $courier->deliveredOrders()->count() * 500;
    }

    /**
     * Статистика по дням
     */
    private function getDailyStats(Courier $courier)
    {
        return $courier->deliveredOrders()
            ->selectRaw('DATE(delivered_at) as date, COUNT(*) as count')
            ->where('delivered_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }
}