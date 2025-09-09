<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminCourierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $couriers = Courier::withCount([
            'assignedOrders as assigned_orders_count',
            'deliveredOrders as delivered_orders_count',
            'assignedOrders as active_orders_count' => function ($query) {
                $query->whereIn('status', ['assigned', 'picked_up', 'in_delivery']);
            }
        ])->orderBy('created_at', 'desc')->get();

        $totalCouriers = Courier::count();
        $activeCouriers = Courier::where('is_active', true)->count();
        $totalDeliveries = Order::where('status', 'delivered')->whereNotNull('delivered_by')->count();

        return view('admin.couriers.index', compact(
            'couriers',
            'totalCouriers',
            'activeCouriers',
            'totalDeliveries'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.couriers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|unique:couriers,login|max:255',
            'phone' => 'required|string|unique:couriers,phone|max:20',
            'email' => 'nullable|email|unique:couriers,email|max:255',
            'password' => 'required|string|min:6',
            'vehicle_type' => 'nullable|string|in:bicycle,motorcycle,car,walking',
            'vehicle_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        // Убираем двойное хеширование - модель сама хеширует пароль
        // $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['is_active'] = $request->has('is_active');

        Courier::create($validatedData);

        return redirect()->route('admin.couriers.index')
            ->with('success', 'Курьер успешно создан!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Courier $courier)
    {
        $courier->load(['assignedOrders', 'deliveredOrders']);
        
        $totalAssigned = $courier->assignedOrders->count();
        $totalDelivered = $courier->deliveredOrders->count();
        $activeOrders = $courier->assignedOrders()
            ->whereIn('status', ['assigned', 'picked_up', 'in_delivery'])
            ->count();

        // Вычисляем среднее время доставки
        $averageDeliveryTime = null;
        $deliveredOrders = $courier->deliveredOrders()
            ->whereNotNull('delivered_at')
            ->get();

        if ($deliveredOrders->count() > 0) {
            $totalDeliveryTime = 0;
            foreach ($deliveredOrders as $order) {
                if ($order->created_at && $order->delivered_at) {
                    $totalDeliveryTime += $order->created_at->diffInMinutes($order->delivered_at);
                }
            }
            $averageDeliveryTime = round($totalDeliveryTime / $deliveredOrders->count());
        }

        return view('admin.couriers.show', compact(
            'courier',
            'totalAssigned',
            'totalDelivered',
            'activeOrders',
            'averageDeliveryTime'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Courier $courier)
    {
        return view('admin.couriers.edit', compact('courier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Courier $courier)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:couriers,login,' . $courier->id,
            'phone' => 'required|string|max:20|unique:couriers,phone,' . $courier->id,
            'email' => 'nullable|email|max:255|unique:couriers,email,' . $courier->id,
            'password' => 'nullable|string|min:6',
            'vehicle_type' => 'nullable|string|in:bicycle,motorcycle,car,walking',
            'vehicle_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if (!empty($validatedData['password'])) {
            // Убираем двойное хеширование - модель сама хеширует пароль
            // $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $validatedData['is_active'] = $request->has('is_active');

        $courier->update($validatedData);

        return redirect()->route('admin.couriers.show', $courier)
            ->with('success', 'Данные курьера успешно обновлены!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Courier $courier)
    {
        // Проверяем, есть ли активные заказы
        $activeOrders = $courier->assignedOrders()
            ->whereIn('status', ['assigned', 'picked_up', 'in_delivery'])
            ->count();

        if ($activeOrders > 0) {
            return redirect()->route('admin.couriers.index')
                ->with('error', 'Нельзя удалить курьера с активными заказами!');
        }

        $courier->delete();

        return redirect()->route('admin.couriers.index')
            ->with('success', 'Курьер успешно удален!');
    }

    /**
     * Toggle courier active status.
     */
    public function toggleStatus(Courier $courier)
    {
        $courier->update([
            'is_active' => !$courier->is_active
        ]);

        $status = $courier->is_active ? 'активирован' : 'деактивирован';
        
        return redirect()->back()
            ->with('success', "Курьер успешно {$status}!");
    }

    /**
     * Get courier statistics for dashboard.
     */
    public function getStatistics()
    {
        $totalCouriers = Courier::count();
        $activeCouriers = Courier::where('is_active', true)->count();
        $totalDeliveries = Order::where('status', 'delivered')->whereNotNull('delivered_by')->count();

        $todayDeliveries = Order::where('status', 'delivered')
            ->whereNotNull('delivered_by')
            ->whereDate('delivered_at', today())
            ->count();

        // Статистика по курьерам
        $courierStats = Courier::withCount([
            'assignedOrders as total_assigned',
            'deliveredOrders as total_delivered'
        ])->get();

        $avgDeliveryTime = null;
        $deliveredOrders = Order::where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->whereNotNull('delivered_by')
            ->get();

        if ($deliveredOrders->count() > 0) {
            $totalTime = 0;
            foreach ($deliveredOrders as $order) {
                if ($order->created_at && $order->delivered_at) {
                    $totalTime += $order->created_at->diffInMinutes($order->delivered_at);
                }
            }
            $avgDeliveryTime = round($totalTime / $deliveredOrders->count());
        }

        return [
            'total_couriers' => $totalCouriers,
            'active_couriers' => $activeCouriers,
            'total_deliveries' => $totalDeliveries,
            'today_deliveries' => $todayDeliveries,
            'average_delivery_time' => $avgDeliveryTime,
            'courier_stats' => $courierStats
        ];
    }

    /**
     * Get daily statistics for charts.
     */
    public function getDailyStats()
    {
        $dailyStats = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered_orders')
            )
            ->whereNotNull('delivered_by')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $dailyStats;
    }

    /**
     * Assign courier to orders in bulk.
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);

        $courier = Courier::findOrFail($request->courier_id);
        
        if (!$courier->is_active) {
            return redirect()->back()
                ->with('error', 'Нельзя назначить заказы неактивному курьеру!');
        }

        Order::whereIn('id', $request->order_ids)
            ->where('status', 'pending')
            ->update([
                'courier_id' => $courier->id,
                'status' => 'assigned'
            ]);

        return redirect()->back()
            ->with('success', 'Заказы успешно назначены курьеру!');
    }

    /**
     * Get courier earnings.
     */
    public function getEarnings(Courier $courier)
    {
        $totalEarnings = $courier->deliveredOrders()->sum('delivery_fee');
        
        $monthlyEarnings = $courier->deliveredOrders()
            ->whereMonth('delivered_at', now()->month)
            ->whereYear('delivered_at', now()->year)
            ->sum('delivery_fee');

        $weeklyEarnings = $courier->deliveredOrders()
            ->whereBetween('delivered_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->sum('delivery_fee');

        $dailyEarnings = $courier->deliveredOrders()
            ->whereDate('delivered_at', today())
            ->sum('delivery_fee');

        return [
            'total' => $totalEarnings,
            'monthly' => $monthlyEarnings,
            'weekly' => $weeklyEarnings,
            'daily' => $dailyEarnings
        ];
    }
}
