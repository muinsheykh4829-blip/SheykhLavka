<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_ru', 
        'description',
        'description_ru',
        'image',
        'link_url',
        'sort_order',
        'is_active',
        'start_date',
        'end_date',
        'target_audience',
        'click_count',
        'view_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sort_order' => 'integer',
        'click_count' => 'integer',
        'view_count' => 'integer'
    ];

    // Scope для активных баннеров
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope для баннеров в текущем периоде
    public function scopeInPeriod($query)
    {
        $now = Carbon::now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $now);
        });
    }

    // Scope для сортировки
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    // Метод для получения активных баннеров для API
    public static function getActiveForApi($targetAudience = 'all')
    {
        return static::active()
            ->inPeriod()
            ->where(function($q) use ($targetAudience) {
                $q->where('target_audience', 'all')
                  ->orWhere('target_audience', $targetAudience);
            })
            ->ordered()
            ->get();
    }

    // Увеличить количество просмотров
    public function incrementViews()
    {
        $this->increment('view_count');
    }

    // Увеличить количество кликов
    public function incrementClicks()
    {
        $this->increment('click_count');
    }

    // Проверка активности баннера в данный момент
    public function isCurrentlyActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();
        
        if ($this->start_date && $this->start_date->gt($now)) {
            return false;
        }
        
        if ($this->end_date && $this->end_date->lt($now)) {
            return false;
        }

        return true;
    }

    // Получить URL изображения
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        
        // Если путь начинается с assets/, значит это прямой путь
        if (str_starts_with($this->image, 'assets/')) {
            return asset($this->image);
        }
        
        // Если путь начинается с uploads/, используем его напрямую
        if (str_starts_with($this->image, 'uploads/')) {
            return asset($this->image);
        }
        
        // Для остальных случаев используем storage
        return asset('storage/' . $this->image);
    }
}
