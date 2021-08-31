<?php declare(strict_types=1); namespace Phprelude\Optics;
use Closure;

/* K Combinator
 * k :: a -> b -> a */
function k($a): Closure {
    return fn($b) => $a;
}

