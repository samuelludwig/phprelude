<?php declare(strict_types=1); namespace Phprelude\Io;
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

function log(array $data, $file_handle = STDOUT): array {
    $timestamp = date('Y-m-d H:i:s');
    $encoded_data
        = json_encode(['timestamp' => $timestamp, 'data' => $data]);
    if (fwrite($file_handle, $encoded_data)) return [ ':ok', $data ];
    return [ ':error', $data ];
}

function llog($file_handle = STDOUT): Closure {
    return fn($x) => log($x, $file_handle);
}

