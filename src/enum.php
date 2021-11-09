<?php declare(strict_types=1); namespace Phprelude\Enum;
require_once __DIR__ . '/core.php';
use \Phprelude\Core as p;
use Closure;

/**
 * Notes
 * -----
 * - filter vs array_filter: the former destroys keys, the latter does not.
 *
 * Types
 * -----
 * any :: mixed
 * predicate :: callable
 * key :: string|int
 */

/* split_array_into_pairs :: array -> array */
function split_into_pairs(array $x): array {
    return array_chunk($x, 2);
}

/* lsplit_array_into_pairs :: () -> (array -> array) */
function lsplit_into_pairs(): Closure {
    return fn($x) => split_into_pairs($x);
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

/* larray_diff :: array -> (array -> array)
 * (array diff LEFT) */
function larray_diff(array $a): Closure {
    return fn($x) => array_diff($x, $a);
}

/* larray_diffr :: array -> (array -> array)
 * (array diff RIGHT) */
function larray_diffr(array $a): Closure {
    return fn($x) => array_diff($a, $x);
}

/**
 * 
 * @param array $list
 * @param int $p
 * @return multitype:multitype:
 * @link http://www.php.net/manual/en/function.array-chunk.php#75022
 */
function partition(array $list, int $p): array {
    if ($p < 1) return [];
    $listlen = count($list);
    $partlen = (int) floor($listlen / $p);
    if ($listlen === 0 || $partlen === 0) return [];
    $partrem = $listlen % $p;
    $partition = array();
    $mark = 0;
    for($px = 0; $px < $p; $px ++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }
    return $partition;
}

function lpartition(int $p): Closure {
  return fn($a) => partition($a, $p);
}

/**
 * Rotates array values to the left, does not preserve indicies or keys.
 *
 * rotate_array :: array -> array */
function rotate(array $a): array {
    array_push($a, array_shift($a));
    return $a;
}

/* lrotate_array :: () -> (array -> array) */
function lrotate(): Closure {
    return fn($x) => rotate($x);
}

function get_random_element(array $a) {
    return array_rand($a, 1);
}

function lget_random_element(): Closure {
    return fn($x) => get_random_element($x);
}

function ltake_col($key): Closure {
    return fn($x) => array_column($x, $key);
}

function lhead(): Closure {
    return fn($x) => head($x);
}

function larray_keys($search_value = false, bool $strict = false): Closure {
    if ($search_value) return fn($x) => array_keys($x, $search_value, $strict);
    return fn($x) => array_keys($x);
}

function larray_filter(callable $predicate): Closure {
    return fn($x) => array_filter($x, $predicate);
}

/* extract_keys_where :: array -> predicate -> array */
function extract_keys_where(array $a, callable $predicate): array {
    return p\pipe([
        larray_filter($predicate),
        larray_keys()
    ])($a);
}

/* lextract_keys_where :: predicate -> (array -> array) */
function lextract_keys_where(callable $predicate): Closure {
    return fn($x) => extract_keys_where($x, $predicate);
}

/* locate :: array -> predicate -> { key : string|int, value :  any } */
function locate(array $a, callable $predicate): array {
    $filtered = array_filter($a, $predicate);

    $key = p\pipe([
        larray_keys(),
        lhead()
    ])($filtered);

    $value = head($filtered);

    return [ $key, $value ];
}

/* llocate
 * :: predicate -> (array -> predicate -> { key : string|int, value :  any }) */
function llocate(callable $predicate): Closure {
    return fn($x) => locate($x, $predicate);
}

/* is_assoc:: array -> bool */
function is_assoc(array $a): bool {
    $count_of_string_keys_in_array = p\pipe([
        larray_keys(),
        lfilter(fn($x) => is_string($x)),
        fn($x) => count($x),
    ])($a);
    return $count_of_string_keys_in_array > 0;
}

/* lis_assoc:: () -> (array -> bool) */
function lis_assoc(): Closure {
    return fn($x) => is_assoc($x);
}

/* extract_where_key_value_matches
 * :: array -> key -> predicate -> assoc */
function extract_where_key_value_matches(
    array $list,
    $key,
    callable $predicate
): array {
    $is_our_element
        = function ($x) use ($key, $predicate) {
            if (!is_assoc($x)) return [];
            return $predicate($x[$key]);
        };

    $element = find($list, $is_our_element);

    if ($element === null) return [];
    return $element;
}

function lextract_where_key_value_matches(
    $key,
    callable $predicate
): Closure {
    return fn($a) => extract_where_key_value_matches($a, $key, $predicate);
}

/* extract_element_from_list_by_contained_key_value
 * :: array -> key -> any -> assoc */
function extract_where_key_value_equals(
    array $list,
    $key,
    $target_value
): array {
    $target_value_type = gettype($target_value);

    $is_our_element
        = function ($x) use ($key, $target_value, $target_value_type) {
            if (!is_assoc($x)) return [];
            settype($x[$key], $target_value_type);
            return $x[$key] === $target_value;
        };

    $element = find($list, $is_our_element);

    if ($element === null) return [];
    return $element;
}

/* lextract_element_from_list_by_contained_key_value
 * :: key -> any -> (array -> array) */
function lextract_where_key_value_equals($key, $target_value): Closure {
    return fn($x) => extract_where_key_value_equals($x, $key, $target_value);
}

/* element_with_key_value_exists
 * :: array -> key -> any -> bool */
function element_with_key_value_exists(array $list, $key, $target_value): bool {
    $is_our_element = lcontains_key_value($key, $target_value);

    return p\pipe([
        lfilter($is_our_element),
        p\not(lempty())
    ])($list);
}

/* lelement_with_key_value_exists_in_list
 * :: key -> any -> (array -> bool) */
function lelement_with_key_value_exists($key, $target_value): Closure {
    return fn($x) => element_with_key_value_exists($x, $key, $target_value);
}

/* get_first_index_where_element_contains_key_value
 * :: array -> string -> any -> int */
function get_first_index_where_element_contains_key_value(
    array $list,
    $key,
    $target_value
) {
    $is_our_element = lcontains_key_value($key, $target_value);

    return p\pipe([
        lfilter($is_our_element),
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
    return fn($x) =>
            get_first_index_where_element_contains_key_value(
                $x, $key, $target_value);
}

/* lempty :: () -> (any -> bool) */
function lempty(): Closure {
    return fn($x) => empty($x);
}

/* contains_key_value :: array -> string -> any -> bool */
function contains_key_value(array $a, $key, $target_value): bool {
    $target_value_type = gettype($target_value);

    $is_associative = fn($x) => count(filter(array_keys($x), 'is_string')) > 0;

    if (!$is_associative($a)) return [];

    settype($a[$key], $target_value_type);
    return $a[$key] === $target_value;
}

/* lcontains_key_value
 * :: string -> any -> (array -> string -> any -> bool) */
function lcontains_key_value($key, $target_value): Closure {
    return fn($x) => contains_key_value($x, $key, $target_value);
}

/* larray_merge :: Variadic array -> (array -> array) */
function larray_merge(...$a): Closure {
    return fn($x) => array_merge($x, ...$a);
}

/* Alias for array_merge */
function merge(...$a): array {
    return array_merge(...$a);
}

function lmerge(...$a): Closure {
    return fn($x) => array_merge($x, ...$a);
}

/* sum_key_values :: Variadic array -> array */
function sum_key_values(...$arrays): array {
    $res = array_merge_recursive(...$arrays);

    foreach($res as $index => $x)
        if (is_array($x)) $res[$index] = array_sum($x);

    return $res;
}

function lsum_key_values(): Closure {
    return fn(...$arrays) => sum_key_values(...$arrays);
}

/* set_key_val :: array -> key -> any -> array */
function set_key_val(array $a, $key, $val): array {
    $a[$key] = $val;
    return $a;
}

/* lset_key_val :: key -> any -> (array -> array) */
function lset_key_val($key, $val): Closure {
    return fn($a) => set_key_val($a, $key, $val);
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
function update_nested_key_val($a, array $keys, callable $f) {
    if (count($keys) === 0) return $f($a);
    [$first_key, $rest_of_keys] = uncons($keys);
    $f_prime = lupdate_nested_key_val($rest_of_keys, $f);
    return update_key_val($a, $first_key, $f_prime);
}

/* lupdate_nested_key_val :: array -> callable/1 -> (array -> array) */
function lupdate_nested_key_val(array $keys, callable $f): Closure {
    return fn($a) => update_nested_key_val($a, $keys, $f);
}

/* extract_key_val :: array -> array -> callable/1 -> array */
function extract_key_val(array $a, $key) {
    return $a[$key];
}

/* lextract_key_val :: array -> array -> callable/1 -> array */
function lextract_key_val($key) {
    return fn($a) => extract_key_val($a, $key);
}

/* extract_nested_key_val :: array -> array -> callable/1 -> array */
function extract_nested_key_val($a, array $keys) {
    if (count($keys) === 0) return $a;
    [$first_key, $rest_of_keys] = uncons($keys);
    $remainder = $a[$first_key];
    return extract_nested_key_val($remainder, $rest_of_keys);
}

/* lextract_nested_key_val :: array -> callable/1 -> (array -> array) */
function lextract_nested_key_val(array $keys): Closure {
    return fn($a) => extract_nested_key_val($a, $keys);
}

/**
 * Splits an array into an array of keys, and an array of values
 *
 * split_array_key_vals :: array -> [ array, array ]
 */
function split_key_vals(array $a): array {
    $keys = array_keys($a);
    $vals = array_values($a);
    return [ $keys, $vals ];
}

/* lsplit_array_key_vals :: () -> (array -> [ array, array ]) */
function lsplit_key_vals(): Closure {
    return fn($x) => split_key_vals($x);
}

/* array_contains_key_vals :: array -> array -> bool */
function contains_key_vals(array $a, array $key_vals): bool {
    $key_value_pair_matches
        = fn($key_name) =>
        array_key_exists($key_name, $a)
            && ($a[$key_name] === $key_vals[$key_name]);

    return p\pipe([
        larray_keys(),
        lis_true_for_all_elements($key_value_pair_matches),
    ])($key_vals);
}

/* larray_contains_key_vals :: array -> (array -> bool) */
function lcontains_key_vals(array $key_vals): Closure {
    return fn($a) => contains_key_vals($a, $key_vals);
}

/* is_true_for_all_elements :: array -> predicate -> bool */
function is_true_for_all_elements(array $a, callable $predicate): bool {
    return fold( $a, true,
            fn($all_match, $elem) =>
                ($all_match == true && $predicate($elem)));
}

/* lis_true_for_all_elements :: predicate -> (array -> bool) */
function lis_true_for_all_elements(callable $predicate): Closure {
    return fn($a) => is_true_for_all_elements($a, $predicate);
}

/* is_true_for_some_element :: array -> predicate -> bool */
function is_true_for_some_element(array $a, callable $predicate): bool {
    return fold( $a, false,
            fn($all_match, $elem) =>
                ($all_match == true || $predicate($elem)));
}

/* lis_true_for_some_element :: predicate -> (array -> bool) */
function lis_true_for_some_element(callable $predicate): Closure {
    return fn($a) => is_true_for_some_element($a, $predicate);
}

/* extract_values_from_array :: array -> array -> array */
function extract_values(array $a, array $keys): array {
    return map($keys, fn($key) => $a[$key]);
}

/* lextract_values_from_array :: array -> (array -> array) */
function lextract_values(array $keys): Closure {
    return fn($a) => extract_values($a, $keys);
}

/* extract_values_from_array_into_format :: array -> array -> array
 * This function accepts two values, a source array, to extract from, and a
 * mapping of new key-names to keys in the source array. A map of `new-key` to
 * `source-key` may also involve a transformation of `source-key`'s value; in
 * which case, `source-key` will be given as an array, the first element being
 * the key name, and the second being the callable to apply to the value.
 * TODO: make this work recursively (i.e., multiple levels deep)? */
function extract_values_into_format(array $a, array $key_format): array {
    p\enforce_constraint(
        $key_format,
        lis_assoc(),
        'Arg 2 expected to be associative array, received '
            . json_encode($key_format, JSON_PRETTY_PRINT));
    [$format_keys, $source_keys] = split_key_vals($key_format);
    $derive_key_value
        = fn($key) => is_array($key) ? $key[1]($a[$key[0]]) : $a[$key];
    $source_values = map($source_keys, $derive_key_value);
    return array_combine($format_keys, $source_values);
}

/* lextract_values_from_array_into_format :: array -> (array -> array) */
function lextract_values_into_format(array $key_format): Closure {
    return fn($a) => extract_values_into_format($a, $key_format);
}

function larray_unique($flags = SORT_STRING): Closure {
    return fn($a) => array_unique($a, $flags);
}

/* filter_unique_arrays :: array -> array */
function filter_unique_arrays(array $arrays): array {
    $serialized = array_map('serialize', $arrays);
    $unique = array_unique($serialized);
    return array_intersect_key($arrays, $unique);
}

/* lfilter_unique_arrays :: () -> (array -> array) */
function lfilter_unique_arrays(): Closure {
    return fn($a) => filter_unique_arrays($a);
}

/* each :: array -> callable -> array */
function each(array $a, callable $f): array {
    $results = [];
    foreach ($a as $v) $results[] = $f($v);
    return $results;
}

/* leach :: callable -> (array -> array) */
function leach(callable $f): Closure {
    return fn($a) => each($a, $f);
}

/* each_with_index :: array -> callable -> array */
function each_with_index(array $a, callable $f): array {
    $results = [];
    foreach ($a as $k => $v) $results[] = $f($k, $v);
    return $results;
}

/* leach_with_index :: callable -> (array -> array) */
function leach_with_index(callable $f): Closure {
    return fn($a) => each_with_index($a, $f);
}

/**
 * An universal array_map for any Traversable
 * and with a "fixed" argument order.
 *
 * @template I
 * @template O
 * @param Traversable|array $list
 * @psalm-param \Traversable<I>|I[] $list
 * @param callable(I, array-key):O $callback
 * @return mixed[]
 * @psalm-return O[]
 */
function map($list, callable $callback): array {
    $agg = [];

    /**
     * @var array-key $key
     */
    foreach ($list as $key => $value) {
        $agg[$key] = $callback($value, $key);
    }

    return $agg;
}

/**
 * Lazy version of map.
 *
 * @template I
 * @template O
 * @param callable(I, array-key): O $callback
 * @return Closure(\Traversable<I>|I[]): O[]
 */
function lmap(callable $callback): Closure {
    return
        /**
         * @param Traversable|array $list
         * @psalm-param \Traversable<I>|I[] $list
         * @return mixed[]
         * @psalm-return O[]
         */
        function ($list) use ($callback): array {
            return map($list, $callback);
        };
}

/* map_with_index :: array -> callable -> array */
function map_with_index(array $a, callable $f): array {
    $agg = [];
    foreach ($a as $k => $v) $agg[$k] = $f($k, $v);
    return $agg;
}

/* lmap_with_index :: callable -> (array -> array) */
function lmap_with_index(callable $f): Closure {
    return fn($a) => map_with_index($a, $f);
}

/**
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param mixed $initial
 * @psalm-param T $initial
 * @param callable(T,T):T $callback
 * @return mixed
 * @psalm-return T
 */
function fold(array $list, $initial, callable $callback) {
    /** @psalm-var T $value */
    $value = $initial;

    foreach ($list as $item) {
        $value = $callback($value, $item);
    }

    return $value;
}

function lfold($initial, callable $callback): Closure {
    return fn($x) => fold($x, $initial, $callback);
}

/* right-fold */
function foldr(array $list, $initial, callable $callback) {
    return p\pipe([
        larray_reverse(),
        lfold($initial, $callback),
    ])($list);
}

function lfoldr($initial, callable $callback) {
    return fn($x) => foldr($x, $initial, $callback);
}

/**
 * Returns the first element on a list after it is sorted. It is a head(sort()) alias.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param callable(T,T):int $test
 * @param mixed|null $if_empty
 * @psalm-param T|null $if_empty
 * @return mixed|null
 * @psalm-return T|null
 */
function first(array $list, callable $test, $if_empty = null) {
    if (empty($list)) {
        return $if_empty;
    }

    return head(sort($list, $test));
}

/**
 * Lazy version of the `first` function.
 *
 * @template T
 * @param callable(T,T):int $test
 * @param mixed|null $if_empty
 * @psalm-param T|null $if_empty
 * @return Closure(T[]):(T|null)
 */
function lfirst(callable $test, $if_empty = null): Closure {
    return function (array $list) use ($test, $if_empty) {
        return first($list, $test, $if_empty);
    };
}

/**
 * Flats a multi-dimensional array.
 *
 * @template T
 * @param mixed[] $list
 * @psalm-param list<T> $list
 * @return mixed[]
 * @psalm-return list<T>
 */
function flatten(array $list): array {
    /** @psalm-var list<T> $flat */
    $flat = [];

    array_walk_recursive(
        $list,
        /**
         * @param mixed $value
         * @psalm-param T $value
         */
        static function ($value) use (&$flat): void {
            /** @psalm-var list<T> $flat */
            $flat[] = $value;
        }
    );

    /** @psalm-var list<T> */
    return $flat;
}

/**
 * Extract the first element of a list.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param mixed|null $default
 * @psalm-param T|null $default
 * @return mixed|null
 * @psalm-return T|null
 */
function head(array $list, $default = null) {
    if (empty($list)) {
        return $default;
    }

    return array_shift($list);
}

/**
 * Extract the last element of a list.
 *
 * @param array $list
 * @param mixed $default
 *
 * @return mixed|null
 */
function last(array $list, $default = null) {
    if (empty($list)) {
        return $default;
    }

    return array_pop($list);
}

/**
 * Extract the elements after the head of a list, which must be non-empty.
 *
 * @param array $list
 *
 * @return array
 */
function tail(array $list) {
    return array_slice($list, 1);
}

/**
 * Return all the elements of a list except the last one. The list must be non-empty.
 *
 * @param array $list
 *
 * @return array
 */
function init(array $list): array {
    return array_slice($list, 0, -1);
}

/**
 * Decompose a list into its head and tail.
 *
 * @param array $list
 * @return array{0: mixed, 1: array}
 */
function uncons(array $list): array {
    return [$list[0], array_slice($list, 1)];
}

/**
 * Filter a list removing null values.
 *
 * @param array $list
 *
 * @return mixed[]
 */
function non_null(array $list): array {
    return array_values(
        array_filter($list, function ($item) {
            return $item !== null;
        })
    );
}

/**
 * Filter a list removing empty values.
 *
 * @param array $list
 * @return array
 */
function non_empty(array $list): array {
    return array_values(
        array_filter($list, function ($item) {
            return !empty($item);
        })
    );
}

/**
 * Lazy version of join().
 *
 * @param string $glue
 * @return Closure(array): string
 */
function ljoin(string $glue = ''): Closure {
    return static function (array $pieces) use ($glue): string {
        return join($glue, $pieces);
    };
}

/**
 * An array_filter that dont preserve keys
 *
 * @template T
 * @param mixed[] $input
 * @psalm-param T[] $input
 * @param callable(T):bool $callback
 * @return mixed[]
 * @psalm-return T[]
 */
function filter(array $input, callable $callback): array {
    return array_values(array_filter($input, $callback));
}

/**
 * Lazy version of filter.
 *
 * @template T
 * @param callable(T):bool $callback
 * @return Closure(T[]):T[]
 */
function lfilter(callable $callback): Closure {
    return function (array $input) use ($callback): array {
        return filter($input, $callback);
    };
}

/**
 * Returns the first element that matches the given predicate.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param callable(T):bool $predicate
 * @param mixed|null $default
 * @psalm-param T|null $default
 * @return mixed|null
 * @psalm-return T|null
 */
function find(array $list, callable $predicate, $default = null) {
    foreach ($list as $item) {
        if ($predicate($item)) {
            return $item;
        }
    }

    return $default;
}

/**
 * Lazy version for find.
 *
 * @template T
 * @param callable(T):bool $predicate
 * @param mixed|null $default
 * @psalm-param T|null $default
 * @return Closure(T[]):(T|null)
 */
function lfind(callable $predicate, $default = null): Closure {
    return function (array $list) use ($predicate, $default) {
        return find($list, $predicate, $default);
    };
}

/**
 * Sorts a list by a given compare/test function returning a new list without modifying the given one.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param callable(T, T):int $test
 * @return array
 * @psalm-return T[]
 */
function sort(array $list, callable $test): array {
    usort($list, $test);
    return $list;
}

/**
 * Lazy version of the sort function.
 *
 * @template T
 * @param callable(T, T):int $test
 * @return Closure(T[]):T[]
 */
function lsort(callable $test): Closure {
    return function (array $list) use ($test) {
        return sort($list, $test);
    };
}

/* take :: array -> int -> Optional bool -> array */
function take(array $a, int $count, bool $preserve_keys = false): array {
    return array_slice($a, 0, $count, $preserve_keys);
}

function ltake(int $count, bool $preserve_keys = false): Closure {
    return fn($a) => take($a, $count, $preserve_keys);
}

/* drop :: array -> int -> Optional bool -> array */
function drop(array $a, int $count, bool $preserve_keys = false): array {
    return array_slice($a, $count, null, $preserve_keys);
}

function ldrop(int $count, bool $preserve_keys = false): Closure {
    return fn($a) => drop($a, $count, $preserve_keys);
}

/* has_keys :: array -> List string -> bool */
function has_keys(array $a, array $key_names): bool {
    return is_true_for_all_elements(
            $key_names, fn($key) => array_key_exists($key, $a));
}

function lhas_keys(array $key_names): Closure {
    return fn($a) => has_keys($a, $key_names);
}

/* We accept one key, or an array of keys */
function remove_keys(array $a, $keys): array {
    if (is_array($keys)) {
        foreach ($keys as $k) { unset($a[$k]); }
        return $a;
    }
    unset($a[$keys]); // only one lone key is provided
    return $a;
}

function lremove_keys($keys): Closure {
    return fn($a) => remove_keys($a, $keys);
}

/* merge_preserve_keys :: Variadic array -> array */
function merge_preserve_keys(...$arrays): array {
    return foldr($arrays, [], fn($a1, $a2) => $a1 + $a2);
}

function lmerge_preserve_keys(): Closure {
    return fn($a) => merge_preserve_keys($a);
}

function larray_reverse($preserve_keys = false): Closure {
    return fn($a) => array_reverse($a, $preserve_keys);
}

function is_subset_of($a, $b): bool {
    return array_diff($a, $b) === [];
}

function lis_subset_of($b): Closure {
    return fn($a) => is_subset_of($a, $b);
}

function is_superset_of($a, $b): bool {
    return array_diff($b, $a) === [];
}

function lis_superset_of($b): Closure {
    return fn($a) => is_superset_of($a, $b);
}

/**
 * Function that groups an array of associative arrays by some key.
 *
 * @param {String} $key Property to sort by.
 * @param {Array} $data Array that stores multiple associative arrays.
 */
function group_by($a, $key) {
    $result = [];

    foreach($a as $val) {
        if(array_key_exists($key, $val)){
            $result[$val[$key]][] = $val;
        }else{
            $result[""][] = $val;
        }
    }

    return $result;
}

function lgroup_by($key): Closure {
    return fn($a) => group_by($a, $key);
}

/** 
 * Zips together two arrays, the $host, and $data, where zipped $data elements
 * are appended to the corresponding $host element as under the $field_name key.
 *
 * The zipping finishes as soon as any enumerable in the given collection
 * completes.
 *
 * Turns elements into an array if they aren't already.
 */
function zip_as_field(
    array $host,
    array $data,
    string $field_name
): array {
    $length = min(count($host), count($data));
    return p\p(
        $host, 
        ltake($length), 
        lmap(fn($x) => is_array($x) ? $x : [$x]),
        lmap_with_index(
            fn($index, $a) => set_key_val($a, $field_name, $data[$index])
        )
    );
}

function lzip_as_field(array $data, string $field_name): Closure {
  return fn($a) => zip_as_field($a, $data, $field_name);
}
