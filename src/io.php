<?php declare(strict_types=1); namespace Phprelude\IO;
use Closure;

/* inspect :: any -> any
 * !impure: prints to stdout */
function inspect($x) {
    var_dump($x);
    return $x;
}

/* linspect :: () -> (any -> any) */
function linspect(): Closure {
    return fn($x) => inspect($x);
}

/* print_out :: any -> any
 * !impure: prints to stdout */
function print_out($r, $x) {
    echo $x;
    return $r;
}

/* lprint_out :: () -> (any -> any) */
function lprint_out($x): Closure {
    return fn($r) => print_out($r, $x);
}
