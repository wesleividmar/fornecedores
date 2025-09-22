<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/\D+/', '', (string) $value);

        if (strlen($cnpj) !== 14) {
            $fail('CNPJ inválido.');

            return;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $fail('CNPJ inválido.');

            return;
        }

        $calc = function (string $base, array $pesos): int {
            $s = 0;
            foreach ($pesos as $i => $p) {
                $s += intval($base[$i]) * $p;
            }
            $r = $s % 11;

            return ($r < 2) ? 0 : 11 - $r;
        };

        $d1 = $calc($cnpj, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $d2 = $calc($cnpj, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        if ((int) $cnpj[12] !== $d1 || (int) $cnpj[13] !== $d2) {
            $fail('CNPJ inválido.');
        }
    }
}
