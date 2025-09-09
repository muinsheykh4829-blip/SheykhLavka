<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'picker_id',
        'completed_by',
        'courier_id',
        'delivered_by',
        'status',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'delivery_address',
        'delivery_phone',
        'delivery_name',
        'delivery_time',
        'delivery_type',
        'comment',
        'picking_started_at',
        'picking_completed_at',
        'delivered_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_time' => 'datetime',
        'picking_started_at' => 'datetime',
        'picking_completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function picker(): BelongsTo
    {
        return $this->belongsTo(Picker::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(Picker::class, 'completed_by');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(Courier::class, 'delivered_by');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'processing' => 'warning',
            'accepted' => 'info',
            'preparing' => 'primary',
            'ready' => 'info',
            'delivering' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getStatusNameAttribute()
    {
        $names = [
            'processing' => 'В обработке',
            'accepted' => 'Принят',
            'preparing' => 'Собирается',
            'ready' => 'Собран',
            'delivering' => 'Курьер в пути',
            'delivered' => 'Завершен',
            'cancelled' => 'Отменен'
        ];

        return $names[$this->status] ?? $this->status;
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('quantity');
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 0, '.', ' ') . ' с.';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }
}
