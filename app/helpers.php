<?php

if (! function_exists('pesos')) {
    /** Formatea un entero como pesos chilenos: 12500 -> $12.500 */
    function pesos(int $monto, bool $signo = false): string
    {
        $texto = '$'.number_format(abs($monto), 0, ',', '.');

        if ($monto < 0) {
            return '−'.$texto;
        }

        return $signo && $monto > 0 ? '+'.$texto : $texto;
    }
}
