<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Picker extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'login',
        'password',
        'name',
        'phone',
        'is_active'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Автоматическое хеширование пароля
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Проверка пароля
     */
    public function checkPassword($password)
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Заказы, взятые в работу этим сборщиком
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'picker_id');
    }

    /**
     * Активные заказы сборщика
     */
    public function activeOrders()
    {
        return $this->orders()->whereIn('status', ['picked_up', 'in_picking']);
    }

    /**
     * Завершенные заказы сборщика
     */
    public function completedOrders()
    {
        return $this->orders()->where('status', 'picked');
    }
}
