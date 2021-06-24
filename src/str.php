<?php declare(strict_types=1); namespace Phprelude\Str;
use Closure;

/**
 * Returns a Closure that concatenates two strings using the given separator.
 *
 * @param string $separator
 *
 * @return Closure(string, string|false|null): string
 */
function concat(string $separator = ''): Closure
{
    return
        /**
         * @param string $a
         * @param string|false|null $b
         * @return string
         */
        static function (string $a, $b) use ($separator): string {
            if ($b === false || $b === null) {
                return $a;
            }

            return "{$a}{$separator}{$b}";
        };
}

/**
 * Returns a lazy version of concat.
 *
 * @param string $separator
 *
 * @return Closure(string|false|null):(Closure(string):string)
 */
function lconcat(string $separator = ''): Closure
{
    return
        /**
         * @param string|false|null $b
         * @return Closure(string): string
         */
        static function ($b) use ($separator): Closure {
            return static function (string $a) use ($separator, $b): string {
                return concat($separator)($a, $b);
            };
        };
}

