<?php

use App\Helpers\CurrencyHelper;

if (!function_exists('format_somoni')) {
    /**
     * Форматирование суммы в сомони
     */
    function format_somoni($amount)
    {
        return CurrencyHelper::formatSomoni($amount);
    }
}

if (!function_exists('tj_date')) {
    /**
     * Форматирование даты для Таджикистана
     */
    function tj_date($date, $format = 'd.m.Y H:i')
    {
        return $date->setTimezone('Asia/Dushanbe')->format($format);
    }
}

if (!function_exists('tj_date_human')) {
    /**
     * Человекочитаемая дата для Таджикистана
     */
    function tj_date_human($date)
    {
        return $date->setTimezone('Asia/Dushanbe')->diffForHumans();
    }
}
