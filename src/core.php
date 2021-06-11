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
 * key :: string|int
 */

/* getenv_with_default :: string -> string -> string
 * !impure: reads from environment */
function getenv_with_default(
    string $environment_variable_name,
    string $default_value
): string {
    $environment_variable_value = getenv($environment_variable_name);
    if ($environment_variable_value === false) return $default_value;
    return $environment_variable_value;
}

/* split_array_into_pairs :: array -> array */
function split_array_into_pairs(array $x): array {
    return array_chunk($x, 2);
}

/* lsplit_array_into_pairs :: () -> (array -> array) */
function lsplit_array_into_pairs(): Closure {
    return fn($x) => split_array_into_pairs($x);
}

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

/* lfile_get_contents :: () -> (string -> string|bool) */
function lfile_get_contents(): Closure {
    return fn($x) => file_get_contents($x);
}

/* json_file_to_array :: string -> array */
function json_file_to_array(string $file_location): array {
    return f\pipe([
        lfile_get_contents(),
        ljson_decode()
    ])($file_location);
}

/* take_key :: array -> key -> Optional any -> any */
function take_key(array $a, $key, $default = null) {
    if (isset($a[$key])) return $a[$key];
    return $default;
}

/* ltake_key :: key -> Optional any -> (array -> any) */
function ltake_key($key, $default = null): Closure {
    return fn($x) => take_key($x, $key, $default);
}

/* larray_diff :: array -> (array -> array) */
function larray_diff(array $a): Closure {
    return fn($x) => array_diff($x, $a);
}

/* larray_diffr :: array -> (array -> array) */
function larray_diffr(array $a): Closure {
    return fn($x) => array_diff($a, $x);
}

/**
 * Rotates array values to the left, does not preserve indicies or keys.
 *
 * rotate_array :: array -> array */
function rotate_array(array $a): array {
    array_push($a, array_shift($a));
    return $a;
}

/* lrotate_array :: () -> (array -> array) */
function lrotate_array(): Closure {
    return fn($x) => rotate_array($x);
}

/* define_constant_from_environment_variable
 * :: string -> string -> array
 * !impure: reads from environment; creates a global constant */
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

/* to_float :: string|int|float -> float */
function to_float($x): float {
    return floatval($x);
}

function lto_float(): Closure {
    return fn($x) => to_float($x);
}

function to_string($x): string {
    return "$x";
}

function lto_string(): Closure {
    return fn($x) => to_string($x);
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
    return fn($x) => bound_val($x, $lower_bound, $upper_bound);
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

/* find_keys_where :: array -> predicate -> array */
function find_keys_where(array $a, callable $predicate): array {
    return f\pipe([
        larray_filter($predicate),
        larray_keys()
    ])($a);
}

/* lfind_keys_where :: predicate -> (array -> array) */
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

/* is_associative :: array -> bool */
function is_associative(array $a): bool {
    $count_of_string_keys_in_array = f\pipe([
        larray_keys(),
        f\lfilter(fn($x) => is_string($x)),
        fn($x) => count($x),
    ])($a);
    return $count_of_string_keys_in_array > 0;
}

/* lis_associative :: () -> (array -> bool) */
function lis_associative(): Closure {
    return fn($x) => is_associative($x);
}

/* extract_element_from_list_where_contained_key_value_matches
 * :: array -> key -> predicate -> assoc */
function extract_element_from_list_where_contained_key_value_matches(
    array $list,
    $key,
    callable $predicate
): array {
    $is_our_element
        = function ($x) use ($key, $predicate) {
            if (!is_associative($x)) return [];
            return $predicate($x[$key]);
        };

    $element = f\find($list, $is_our_element);

    if ($element === null) return [];
    return $element;
}

/* extract_element_from_list_by_contained_key_value
 * :: array -> key -> any -> assoc */
function extract_element_from_list_by_contained_key_value(
    array $list,
    $key,
    $target_value
): array {
    $target_value_type = gettype($target_value);

    $is_our_element
        = function ($x) use ($key, $target_value, $target_value_type) {
            if (!is_associative($x)) return [];
            settype($x[$key], $target_value_type);
            return $x[$key] === $target_value;
        };

    $element = f\find($list, $is_our_element);

    if ($element === null) return [];
    return $element;
}

/* lextract_element_from_list_by_contained_key_value
 * :: key -> any -> (array -> array) */
function lextract_element_from_list_by_contained_key_value(
    $key,
    $target_value
): Closure {
    return fn($x) => extract_element_from_list_by_contained_key_value(
                        $x,
                        $key,
                        $target_value);
}

/* element_with_key_value_exists_in_list
 * :: array -> key -> any -> bool */
function element_with_key_value_exists_in_list(
    array $list,
    $key,
    $target_value
): bool {
    $is_our_element
        = llist_element_contains_key_value($key, $target_value);

    return f\pipe([
        f\lfilter($is_our_element),
        f\not(lempty())
    ])($list);
}

/* lelement_with_key_value_exists_in_list
 * :: key -> any -> (array -> bool) */
function lelement_with_key_value_exists_in_list(
    $key,
    $target_value
): Closure {
    return fn($x) => element_with_key_value_exists_in_list(
                        $x,
                        $key,
                        $target_value);
}

/* get_first_index_where_element_contains_key_value
 * :: array -> string -> any -> int */
function get_first_index_where_element_contains_key_value(
    array $list,
    $key,
    $target_value
) {
    $is_our_element
        = llist_element_contains_key_value($key, $target_value);

    return f\pipe([
        f\lfilter($is_our_element),
        larray_keys(),
        lhead()
    ])($list);
}

/* lget_first_index_where_element_contains_key_value
 * :: string -> any -> (array -> int) */
function lget_first_index_where_element_contains_key_value(
    $key,
    $target_value
): Closure {
    return
        fn($x) => get_first_index_where_element_contains_key_value(
                    $x,
                    $key,
                    $target_value);
}

/* lempty :: () -> (any -> bool) */
function lempty(): Closure {
    return fn($x) => empty($x);
}

/* list_element_contains_key_value :: array -> string -> any -> bool */
function list_element_contains_key_value(
    array $element,
    $key,
    $target_value
): bool {
    $target_value_type = gettype($target_value);

    $is_associative
        = fn($a) => count(f\filter(array_keys($a), 'is_string')) > 0;

    if (!$is_associative($element)) return [];

    settype($element[$key], $target_value_type);
    return $element[$key] === $target_value;
}

/* llist_element_contains_key_value
 * :: string -> any -> (array -> string -> any -> bool) */
function llist_element_contains_key_value(
    $key,
    $target_value
): Closure {
    return
        fn($x) => list_element_contains_key_value(
                    $x,
                    $key,
                    $target_value);
}

/* larray_merge :: Variadic array -> (array -> array) */
function larray_merge(...$a): Closure {
    return fn($x) => array_merge($x, ...$a);
}

/* sum_array_key_values :: Variadic array -> array */
function sum_array_key_values(...$arrays): array {
    $res = array_merge_recursive(...$arrays);

    foreach($res as $index => $x)
        if (is_array($x)) $res[$index] = array_sum($x);

    return $res;
}

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

/* is_null_unset_or_empty :: any -> bool */
function is_null_unset_or_empty($x): bool {
    return ($x === null || empty($x) || !isset($x));
}

/* lis_null_unset_or_empty :: () -> (any -> bool) */
function lis_null_unset_or_empty(): Closure {
    return fn($x) => is_null_unset_or_empty($x);
}

/* update_key_val :: array -> key -> callable/1 -> array */
function update_key_val(array $a, $key, callable $f): array {
    $old_val = $a[$key];
    $a[$key] = $f($old_val);
    return $a;
}

/* lupdate_key_val :: key -> callable/1 -> (array -> array) */
function lupdate_key_val($key, callable $f): Closure {
    return fn($a) => update_key_val($a, $key, $f);
}

/* update_nested_key_val :: array -> array -> callable/1 -> array */
function update_nested_key_val(array $a, array $keys, callable $f): array {
    if (count($keys) === 0) return $f($a);
    [$first_key, $rest_of_keys] = f\uncons($keys);
    $f_prime = lupdate_nested_key_val($rest_of_keys, $f);
    return update_key_val($a, $first_key, $f_prime);
}

/* lupdate_nested_key_val :: array -> callable/1 -> (array -> array) */
function lupdate_nested_key_val(array $keys, callable $f): Closure {
    return fn($a) => update_nested_key_val($a, $keys, $f);
}

/**
 * Splits an array into an array of keys, and an array of values
 *
 * split_array_key_vals :: array -> [ array, array ]
 */
function split_array_key_vals(array $a): array {
    $keys = array_keys($a);
    $vals = array_values($a);
    return [ $keys, $vals ];
}

/* lsplit_array_key_vals :: () -> (array -> [ array, array ]) */
function lsplit_array_key_vals(): Closure {
    return fn($x) => split_array_key_vals($x);
}

/* array_contains_key_vals :: array -> array -> bool */
function array_contains_key_vals(array $a, array $key_vals): bool {
    $key_value_pair_matches
        = fn($key_name) => ($a[$key_name] === $key_vals[$key_name]);

    return f\pipe([
        larray_keys(),
        lis_true_for_all_elements($key_value_pair_matches),
    ])($key_vals);
}

/* larray_contains_key_vals :: array -> (array -> bool) */
function larray_contains_key_vals(array $key_vals): Closure {
    return fn($a) => array_contains_key_vals($a, $key_vals);
}

/* is_true_for_all_elements :: array -> predicate -> bool */
function is_true_for_all_elements(array $a, callable $predicate): bool {
    return f\fold( $a, true,
            fn($elem, $all_match) =>
                ($all_match === true && $predicate($elem)));
}

/* lis_true_for_all_elements :: predicate -> (array -> bool) */
function lis_true_for_all_elements(callable $predicate): Closure {
    return fn($a) => is_true_for_all_elements($a, $predicate);
}
