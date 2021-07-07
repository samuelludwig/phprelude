<?php declare(strict_types=1); namespace Phprelude\Number;
use Closure;

/**
 * Returns true if the given number is even.
 *
 * @param int $number
 * @return bool
 */
function even(int $number): bool {
    return $number % 2 === 0;
}

/**
 * Returns true if the given number is odd.
 *
 * @param int $number
 * @return bool
 */
function odd(int $number): bool {
    return !even($number);
}

function to_timestamp(int $x): string {
    return date('Y-m-d H:i:s', $x);
}

function lto_timestamp(): Closure {
    return fn($x) => to_timestamp($x);
}
