<?php declare(strict_types=1); namespace Phprelude\Json;
require_once __DIR__ . '/core.php';
require_once __DIR__ . '/file.php';
use \Phprelude\Core;
use \Phprelude\File;
use Closure;

/* decode :: string -> array */
function decode(string $x): array {
    return json_decode($x, true);
}

/* ldecode :: () -> (string -> array) */
function ldecode(): Closure {
    return fn($x) => json_decode($x, true);
}

/* lencode :: Optional bool -> (array -> string) */
function encode($x, $pretty_print = false): string {
    if ($pretty_print === true)
        return json_encode($x, JSON_PRETTY_PRINT);

    return json_encode($x);
}

/* lencode :: Optional bool -> (array -> string) */
function lencode($pretty_print = false): Closure {
    if ($pretty_print === true)
        return fn($x) => json_encode($x, JSON_PRETTY_PRINT);

    return fn($x) => json_encode($x);
}

/* json_file_to_array :: string -> array */
function json_file_to_array(string $file_location): array {
    return Core\pipe([
        File\lfile_get_contents(),
        ldecode()
    ])($file_location);
}
