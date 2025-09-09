<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ru',
        'slug',
        'description',
        'short_description',
        'price',
        'discount_price',
        'unit',
        'product_type',
        'stock_quantity',
        'stock_quantity_current',
        'stock_quantity_reserved',
        'stock_quantity_minimum',
        'stock_unit',
        'conversion_factor',
        'auto_deactivate_on_zero',
        'stock_updated_at',
        'sku',
        'barcode',
        'images',
        'category_id',
        'is_active',
        'is_featured',
        'weight',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'weight' => 'decimal:3',
            'stock_quantity_current' => 'decimal:3',
            'stock_quantity_reserved' => 'decimal:3',
            'stock_quantity_minimum' => 'decimal:3',
            'conversion_factor' => 'decimal:3',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'auto_deactivate_on_zero' => 'boolean',
            'images' => 'array',
            'attributes' => 'array',
            'stock_updated_at' => 'datetime',
        ];
    }

    // Связи
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Автоматическая генерация slug при создании
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = \Str::slug($product->name);
                
                // Проверяем уникальность slug
                $originalSlug = $product->slug;
                $counter = 1;
                while (static::where('slug', $product->slug)->exists()) {
                    $product->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
        
        static::updating(function ($product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = \Str::slug($product->name);
                
                // Проверяем уникальность slug (исключая текущую запись)
                $originalSlug = $product->slug;
                $counter = 1;
                while (static::where('slug', $product->slug)->where('id', '!=', $product->id)->exists()) {
                    $product->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity_current', '>', 0);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Аксессоры
    public function getCurrentPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    public function getDiscountPercentAttribute()
    {
        if (!$this->discount_price) {
            return 0;
        }
        
        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    // Методы для управления складскими запасами
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getAvailableStockAttribute()
    {
        return $this->stock_quantity_current - $this->stock_quantity_reserved;
    }

    public function isInStock($quantity = 1)
    {
        return $this->getAvailableStockAttribute() >= $quantity;
    }

    public function reserveStock($quantity, $orderId = null)
    {
        if (!$this->isInStock($quantity)) {
            throw new \Exception("Insufficient stock for product: {$this->name}");
        }

        $this->stock_quantity_reserved += $quantity;
        $this->save();

        StockMovement::create([
            'product_id' => $this->id,
            'type' => 'reserved',
            'quantity' => $quantity,
            'quantity_before' => $this->stock_quantity_current, // текущее количество не меняется при резерве
            'quantity_after' => $this->stock_quantity_current,
            'order_id' => $orderId,
            'reference_type' => $orderId ? 'order' : null,
            'reference_id' => $orderId,
            'reason' => $orderId ? "Reserved for order #{$orderId}" : 'Reserved'
        ]);

        return true;
    }

    public function deductStock($quantity, $orderId = null)
    {
        if ($this->stock_quantity_current < $quantity) {
            throw new \Exception("Cannot deduct more stock than available for product: {$this->name}");
        }

        $this->stock_quantity_current -= $quantity;
        
        // Если запас был зарезервирован, уменьшаем резерв
        if ($this->stock_quantity_reserved >= $quantity) {
            $this->stock_quantity_reserved -= $quantity;
        }

        // Проверяем автодеактивацию
        if ($this->auto_deactivate_on_zero && $this->stock_quantity_current <= 0) {
            $this->is_active = false;
        }

        $this->save();

        StockMovement::create([
            'product_id' => $this->id,
            'type' => 'outgoing',
            'quantity' => $quantity,
            'quantity_before' => $this->stock_quantity_current + $quantity, // до списания
            'quantity_after' => $this->stock_quantity_current,
            'order_id' => $orderId,
            'reference_type' => $orderId ? 'order' : null,
            'reference_id' => $orderId,
            'reason' => $orderId ? "Deducted for order #{$orderId}" : 'Manual deduction'
        ]);

        return true;
    }

    public function addStock($quantity, $reason = null)
    {
        $this->stock_quantity_current += $quantity;
        
        // Если товар был деактивирован из-за отсутствия, активируем его
        if (!$this->is_active && $this->auto_deactivate_on_zero && $this->stock_quantity_current > 0) {
            $this->is_active = true;
        }

        $this->save();

        StockMovement::create([
            'product_id' => $this->id,
            'type' => 'incoming',
            'quantity' => $quantity,
            'quantity_before' => $this->stock_quantity_current - $quantity,
            'quantity_after' => $this->stock_quantity_current,
            'reason' => $reason ?: 'Stock added'
        ]);

        return true;
    }

    public function adjustStock($newQuantity, $reason = null)
    {
        $oldQuantity = $this->stock_quantity_current;
        $difference = $newQuantity - $oldQuantity;

        $this->stock_quantity_current = $newQuantity;
        
        // Проверяем автодеактивацию
        if ($this->auto_deactivate_on_zero && $newQuantity <= 0) {
            $this->is_active = false;
        } elseif (!$this->is_active && $this->auto_deactivate_on_zero && $newQuantity > 0) {
            $this->is_active = true;
        }

        $this->save();

        StockMovement::create([
            'product_id' => $this->id,
            'type' => 'adjustment',
            'quantity' => $difference,
            'quantity_before' => $oldQuantity,
            'quantity_after' => $this->stock_quantity_current,
            'reason' => $reason ?: "Stock adjustment from {$oldQuantity} to {$newQuantity}"
        ]);

        return true;
    }

    public function releaseReservedStock($quantity, $orderId = null)
    {
        if ($this->stock_quantity_reserved < $quantity) {
            throw new \Exception("Cannot release more reserved stock than available for product: {$this->name}");
        }

        $this->stock_quantity_reserved -= $quantity;
        $this->save();

        StockMovement::create([
            'product_id' => $this->id,
            'type' => 'unreserved',
            'quantity' => $quantity,
            'quantity_before' => $this->stock_quantity_current,
            'quantity_after' => $this->stock_quantity_current,
            'order_id' => $orderId,
            'reference_type' => $orderId ? 'order' : null,
            'reference_id' => $orderId,
            'reason' => $orderId ? "Unreserved stock for order #{$orderId}" : 'Unreserved'
        ]);

        return true;
    }

    public function scopeAvailableStock($query)
    {
        return $query->whereRaw('stock_quantity_current > stock_quantity_reserved');
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity_current <= stock_quantity_minimum');
    }
}
