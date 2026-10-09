<?php

if (! function_exists('currency')) {
    function currency(float|int|string $amount): string
    {
        try {
            $symbol = \App\Models\Setting::get('currency_symbol', '$');
        } catch (\Throwable) {
            $symbol = '$';
        }

        return $symbol . number_format((float) $amount, 2);
    }
}
