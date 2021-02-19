<?php declare(strict_types=1); namespace Core;
require_once __DIR__ . '/../vendor/autoload.php';
use \Siler\Functional as f;
use Closure;

function getenv_with_default(
    string $environment_variable_name,
    string $default_value
): string {
    $environment_variable_value = getenv($environment_variable_name);
    if ($environment_variable_value === false) return $default_value;
    return $environment_variable_value;
}

function split_array_into_pairs(array $x): array {
    return array_chunk($x, 2);
}

function lsplit_array_into_pairs(): Closure {
    return fn($x) => split_array_into_pairs($x);
}

function ljson_decode(): Closure {
    return fn($x) => json_decode($x, true);
}

function lfile_get_contents(): Closure {
    return fn($x) => file_get_contents($x);
}

function json_file_to_array(string $file_location): array {
    return f\pipe([
        lfile_get_contents(),
        ljson_decode()
    ])($file_location);
}

function ltake_key(string $key): Closure {
    return fn($x) => $x[$key];
}

/**
 * Rotates array values to the left, does not preserve indicies or keys.
 */
function rotate_array(array $a): array {
    array_push($a, array_shift($a));
    return $a;
}

function lrotate_array(): Closure {
    return fn($x) => rotate_array($x);
}

function define_constant_from_environment_variable(
    string $environment_variable_name,
    string $default_environment_variable_value
): array {
    $environment_variable_value =
        getenv_with_default(
            $environment_variable_name,
            $default_environment_variable_value);

    define($environment_variable_name, $environment_variable_value);

    return [ ':ok', true ];
}

function get_random_element_from_array(array $a) {
    return array_rand($a, 1);
}

function lget_random_element_from_array(): Closure {
    return fn($x) => get_random_element_from_array($x);
}

function lfold($initial, callable $callback) {
    return fn($x) => f\fold($x, $initial, $callback);
}

function to_int($x): int {
    return intval($x);
}

function lto_int(): Closure {
    return fn($x) => to_int($x);
}

function to_timestamp(int $x): string {
    return date('Y-m-d H:i:s', $x);
}

function lto_timestamp(): Closure {
    return fn($x) => to_timestamp($x);
}

function ltake_col($key): Closure {
    return fn($x) => array_column($x, $key);
}

function to_float($x): float {
    return floatval($x);
}

function lto_float(): Closure {
    return fn($x) => to_float($x);
}

function lhead(): Closure {
    return fn($x) => f\head($x);
}

function lmax(...$args): Closure {
    return fn($x) => max(f\flatten([$x, $args]));
}

function lmin(...$args): Closure {
    return fn($x) => min(f\flatten([$x, $args]));
}

function bound_val($x, $lower_bound, $upper_bound): Closure {
    return f\pipe([
        lmax($lower_bound),
        lmin($upper_bound)
    ])($x);
}

function lbound_val($lower_bound, $upper_bound): Closure {
    fn($x) => bound_val($x, $lower_bound, $upper_bound);
}

function larray_keys($search_value = false, bool $strict = false): Closure {
    if ($search_value) return fn($x) => array_keys($x, $search_value, $strict);
    return fn($x) => array_keys($x);
}
