<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Форматирование суммы в сомони
     */
    public static function formatSomoni($amount)
    {
        return number_format($amount, 2, '.', ' ') . ' сом.';
    }
    
    /**
     * Форматирование суммы в сомони (короткая форма)
     */
    public static function formatSomoniShort($amount)
    {
        return number_format($amount, 2, '.', ' ') . ' сом.';
    }
}
