<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Courier extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'login',
        'password',
        'name',
        'phone',
        'vehicle_type',
        'vehicle_number',
        'email',
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
        // Поддерживаем оба формата хеширования
        if (Hash::check($password, $this->password)) {
            return true;
        }
        return password_verify($password, $this->password);
    }

    /**
     * Заказы, назначенные этому курьеру (взятые в работу)
     */
    public function assignedOrders()
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    /**
     * Заказы, назначенные этому курьеру (старый метод для совместимости)
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    /**
     * Заказы, доставленные этим курьером
     */
    public function deliveredOrders()
    {
        return $this->hasMany(Order::class, 'delivered_by');
    }
}
