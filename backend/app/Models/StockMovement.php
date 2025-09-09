<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',            // incoming, outgoing, adjustment, reserved, unreserved
        'quantity',        // изменившееся количество (плюс/минус или резерв)
        'quantity_before',
        'quantity_after',
        'reason',          // текстовая причина
        'order_id',
        'reference_type',
        'reference_id',
        'metadata',
        'created_by'
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'metadata' => 'array',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
