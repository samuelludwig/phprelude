<?php declare(strict_types=1); namespace Phprelude\File;
use Closure;

/* lfile_get_contents :: () -> (string -> string|bool) */
function lfile_get_contents(): Closure {
    return fn($x) => file_get_contents($x);
}
