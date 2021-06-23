<?php declare(strict_types=1); namespace Phprelude\Math;
use Closure;

/**
 * Sums two integers.
 *
 * @param int $a
 * @param int $b
 * @return int
 */
function sum(int $a, int $b): int
{
    return $a + $b;
}

/**
 * Sum of $left and $right.
 *
 * @param int|float $right
 * @return Closure(int|float): (int|float)
 */
function add($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left + $right;
        };
}

/**
 * Product of $left and $right.
 *
 * @param int|float $right
 * @return Closure(int|float): (int|float)
 */
function mul($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left * $right;
        };
}

/**
 * Difference of $left and $right.
 *
 * @param int|float $right
 *
 * @return Closure(int|float): (int|float)
 */
function sub($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left - $right;
        };
}

/**
 * Quotient of $left and $right.
 *
 * @param int|float $right
 *
 * @return Closure(int|float): (int|float)
 */
function div($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left / $right;
        };
}

/**
 * Remainder of $left divided by $right.
 *
 * @param int|float $right
 * @return Closure(int|float): (int|float)
 */
function mod($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left % $right;
        };
}
