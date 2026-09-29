<?php

if (! function_exists('price')) {
    /**
     * Format a money amount for display with thousands separators, dropping the
     * decimals when the amount is a whole number: 1450.00 renders as "1,450".
     *
     * A real fractional part is kept rather than rounded away, so an unexpected
     * 1450.5 still shows as "1,450.5" instead of being silently truncated.
     *
     * The currency symbol is deliberately not included, since it is a per-store
     * setting; views prepend their own $cur / $currency.
     */
    function price($value): string
    {
        $formatted = number_format((float) $value, 2, '.', ',');

        if (str_ends_with($formatted, '.00')) {
            return substr($formatted, 0, -3);
        }

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
