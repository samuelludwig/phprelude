<?php declare(strict_types=1); namespace Phprelude\Json;
use Closure;

/* ljson_decode :: () -> (string -> array) */
function ljson_decode(): Closure {
    return fn($x) => json_decode($x, true);
}

/* ljson_encode :: Optional bool -> (array -> string) */
function ljson_encode($pretty_print = false): Closure {
    if ($pretty_print === true)
        return fn($x) => json_encode($x, JSON_PRETTY_PRINT);

    return fn($x) => json_encode($x);
}

/* json_file_to_array :: string -> array */
function json_file_to_array(string $file_location): array {
    return f\pipe([
        lfile_get_contents(),
        ljson_decode()
    ])($file_location);
}
