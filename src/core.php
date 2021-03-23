<?php declare(strict_types=1); namespace Phprelude\Core;
require_once __DIR__ . '/../vendor/autoload.php';
use \Siler\Functional as f;
use Closure;

/**
 * Notes
 * -----
 * - f\filter vs array_filter: the former destroys keys, the latter does not.
 *
 * Types
 * -----
 * any :: mixed
 * predicate :: callable
 * key :: any
 */

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

function ljson_encode($pretty_print = false): Closure {
    if ($pretty_print === true)
        return fn($x) => json_encode($x, JSON_PRETTY_PRINT);

    return fn($x) => json_encode($x);
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

function lfold($initial, callable $callback): Closure {
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

function larray_filter(callable $predicate): Closure {
    return fn($x) => array_filter($x, $predicate);
}

/* bind_error :: callable -> { status : string, result : any } -> any */
function bind_error(callable $f, array $maybe_tuple) {
    [ $status, $value ] = $maybe_tuple;

    if (!is_string($status)) {
        trigger_error(
            __FUNCTION__
            . ' expects the first value of $maybe_tuple to be a string; '
            . gettype($status)
            . ' given'
            , E_PARSE);
    }

    $arg_count = count($maybe_tuple);
    if ($arg_count !== 2) {
        trigger_error(
            __FUNCTION__
            . ' expects $maybe_tuple to consist of only two values, a $status'
            . ' string, and a value (or array of values); '
            . $arg_count
            . ' given'
            , E_PARSE);
    }

    if ($status === ':error') return $maybe_tuple;
    return $f($value);
}

/* lbind_error
 * :: callable -> (callable -> [ status :: string, result :: any ] -> any) */
function lbind_error(callable $f): Closure {
    return fn($maybe_tuple) => bind_error($f, $maybe_tuple);
}

function find_keys_where(array $a, callable $predicate): array {
    return f\pipe([
        larray_filter($predicate),
        larray_keys()
    ])($a);
}

function lfind_keys_where(callable $predicate): Closure {
    return fn($x) => find_keys_where($x, $predicate);
}

/* locate :: array -> predicate -> { key : string|int, value :  any } */
function locate(array $a, callable $predicate): array {
    $filtered = array_filter($a, $predicate);

    $key = f\pipe([
        larray_keys(),
        lhead()
    ])($filtered);

    $value = f\head($filtered);

    return [ $key, $value ];
}

/* llocate
 * :: predicate -> (array -> predicate -> { key : string|int, value :  any }) */
function llocate(callable $predicate): Closure {
    return fn($x) => locate($x, $predicate);
}

/* extract_element_from_list_by_contained_key_value
 * :: List assoc -> string -> any -> assoc */
function extract_element_from_list_by_contained_key_value(
    array $list,
    string $key_name,
    $target_value
): array {
    $target_value_type = gettype($target_value);

    $is_our_element
        = function ($x) use ($key_name, $target_value, $target_value_type) {
            $is_associative
                = fn($a) => count(f\filter(array_keys($a), 'is_string')) > 0;

            if (!$is_associative($x)) return [];
            settype($x[$key_name], $target_value_type);
            return $x[$key_name] === $target_value;
        };

    $element = f\pipe([
        f\lfilter($is_our_element),
        lhead()
    ])($list);

    if ($element === null) return [];
    return $element;
}

function lextract_element_from_list_by_contained_key_value(
    string $key_name,
    $target_value
): Closure {
    return fn($x) => extract_element_from_list_by_contained_key_value(
                        $x,
                        $key_name,
                        $target_value);
}

function element_with_key_value_exists_in_list(
    array $list,
    string $key_name,
    $target_value
): bool {
    $is_our_element
        = llist_element_contains_key_value($key_name, $target_value);

    return f\pipe([
        f\lfilter($is_our_element),
        f\not(lempty())
    ])($list);
}

function lelement_with_key_value_exists_in_list(
    string $key_name,
    $target_value
): Closure {
    return fn($x) => element_with_key_value_exists_in_list(
                        $x,
                        $key_name,
                        $target_value);
}

function get_first_index_where_element_contains_key_value(
    array $list,
    string $key_name,
    $target_value
) {
    $is_our_element
        = llist_element_contains_key_value($key_name, $target_value);

    return f\pipe([
        f\lfilter($is_our_element),
        larray_keys(),
        lhead()
    ])($list);
}

function lget_first_index_where_element_contains_key_value(
    string $key_name,
    $target_value
): Closure {
    return
        fn($x) => get_first_index_where_element_contains_key_value(
                    $x,
                    $key_name,
                    $target_value);
}

function lempty(): Closure {
    return fn($x) => empty($x);
}

/* list_element_contains_key_value :: array -> string -> any -> bool */
function list_element_contains_key_value(
    array $element,
    string $key_name,
    $target_value
): bool {
    $target_value_type = gettype($target_value);

    $is_associative
        = fn($a) => count(f\filter(array_keys($a), 'is_string')) > 0;

    if (!$is_associative($element)) return [];

    settype($element[$key_name], $target_value_type);
    return $element[$key_name] === $target_value;
}

/* llist_element_contains_key_value
 * :: string -> any -> (array -> string -> any -> bool) */
function llist_element_contains_key_value(
    string $key_name,
    $target_value
): Closure {
    return
        fn($x) => list_element_contains_key_value(
                    $x,
                    $key_name,
                    $target_value);
}

/* is_null_unset_or_empty :: any -> bool */
function is_null_unset_or_empty($x): bool {
    return ($x === null || empty($x) || !isset($x));
}

/* lis_null_unset_or_empty :: () -> (any -> bool) */
function lis_null_unset_or_empty(): Closure {
    return fn($x) => is_null_unset_or_empty($x);
}
