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

/* substring_exists :: string -> string -> bool */
function substring_exists(string $haystack, string $needle): bool {
    return strpos($haystack, $needle) !== false;
}

function lsubstring_exists(string $needle): Closure {
    return fn($s) => substring_exists($s, $needle);
}

/* Alias for str_replace */
function replace($subject, $target, $replacement): string {
    return str_replace($target, $replacement, $subject);
}

function lreplace($target, $replacement): Closure {
    return fn($s) => str_replace($target, $replacement, $s);
}

function lstr_replace($target, $replacement): Closure {
    return fn($s) => str_replace($target, $replacement, $s);
}

function lpreg_replace(
    $target_pattern,
    $replacement,
    int $limit = -1
): Closure {
    return fn($s) => preg_replace($target_pattern, $replacement, $s, $limit);
}

/* Aliases for case-coercing functions */
function to_lower($s): string {
    return strtolower($s);
}

function lto_lower(): Closure {
    return fn($s) => strtolower($s);
}

function to_upper($s): string {
    return strtoupper($s);
}

function lto_upper(): Closure {
    return fn($s) => strtoupper($s);
}
