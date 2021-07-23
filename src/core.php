<?php declare(strict_types=1); namespace Phprelude\Core;
require_once __DIR__ . '/enum.php';
use Phprelude\Enum;
use Phprelude\Json;

/*
 * In computer science, functional programming is a programming paradigm
 * a style of building the structure and elements of computer programs
 * that treats computation as the evaluation of mathematical functions
 * and avoids changing-state and mutable data.
 */

use Closure;

/* TODO: Come up with recursive-dir-eval method */
function require_directory($path) {
    foreach (glob("{$path}/*.php") as $filename) {
        require_once $filename;
    }
    return ':ok';
}

/* -- impure: Adds a definition to the parent namespace */
/* Allowed types:
 *   'array', 'bool', 'callable', 'int', 'float',
 *   'object', 'resource', 'string', 'null', 'mixed',
 *   <any other struct> */
function defstruct($struct_name, $struct): array {
    /* Validate types given */
    if (!is_struct($struct)) {
        trigger_error(
            'Invalid type provided to '
                . __FUNCTION__ . ' while processing ' . $struct_name
                . ' with input ' . Json\encode($struct, true), E_USER_ERROR);
    }

    define($struct_name, $struct);
    return [ ':ok', true ];
}

function mk(string $struct_name, array $key_vals = []): array {
    $our_struct = constant($struct_name);
    $defaults = [];

    foreach ($our_struct as $key => $value) {
        if (!isset($value[1])) continue;
        $defaults[$key] = $value[1];
    }

    $result = array_merge($defaults, $key_vals);

    if (!is_type($struct_name, $result))
        trigger_error('Struct is not complete', E_USER_ERROR);

    return $result;
}

function lmk(string $struct_name): Closure {
    return fn($a) => mk($struct_name, $a);
}

/* Alias for mk */
function mkstruct(string $struct_name, array $key_vals = []): array {
    return mk($struct_name, $key_vals);
}

function lmkstruct(string $struct_name): Closure {
    return fn($a) => mk($struct_name, $a);
}

function is_type(string $type_name, $x): bool {
    $matches_struct_pattern = function() use ($type_name, $x) {
        $matches = true;

        $target_struct = constant($type_name);

        foreach ($target_struct as $key => $type_list) {
            $matches
                = $matches
                && Enum\is_true_for_some_element(
                    $type_list[0], fn($t) => is_type($t, $x[$key]));
        }
        return $matches;
    };
    /* Check that $x contains all the keys of the struct -- recursively */
    /* Check that the value of each key matches at least one of the possible
     * types for that key in the struct */
    /* For each key_val, compile list of possible types, and then run
     * is_<type>/is_type(<struct_name>, ...) until a true is reached, or options
     * are exausted, in which case return false. */
    /* Check if its a typed array first */
    $array_type_parse_attempt = explode('array:', $type_name);
    $is_typed_array
        = count($array_type_parse_attempt) === 2
        && $array_type_parse_attempt[1] !== '';
    if ($is_typed_array) {
        $array_type = trim($array_type_parse_attempt[1]);
        return Enum\is_true_for_all_elements($x, fn($x) => is_type($array_type, $x));
    }

    if (count(explode('array:', $type_name)))
    return match ($type_name) {
        'array' => is_array($x),
        'bool' => is_bool($x),
        'callable' => is_callable($x),
        'int' => is_int($x),
        'float' => is_float($x),
        'object' => is_object($x),
        'resource' => is_resource($x),
        'string' => is_string($x),
        'null' => is_null($x),
        'mixed' => true,
        default => $matches_struct_pattern()
    };
}

function lis_type(string $type_name): Closure {
    return fn($x) => is_type($type_name, $x);
}

function enforce_type(string $type_name, $x) {
    if (!is_type($type_name, $x)) {
        trigger_error(
            'Incorrect type given, expected type $type_name, instead received: '
                . json_encode($x, JSON_PRETTY_PRINT),
            E_USER_ERROR);
    }
    return $x;
}

function lenforce_type(string $type_name): Closure {
    return fn($x) => enforce_type($type_name, $x);
}

/* A struct is an array where the value for each key is a list of valid types. */
function is_struct($t): bool {
    if (!Enum\is_assoc($t)) return false;

    $core_types
        = [ 'array', 'bool', 'callable', 'int', 'float', 'object', 'resource'
          , 'string', 'null', 'mixed' ];

    $valid_types
        = array_merge(
            $core_types, array_keys(get_defined_constants(true)['user']));

    /* $value will either be a sub-array or a list of one or more valid types. */
    foreach ($t as $value) {
        if (Enum\is_assoc($value) && !is_struct($value)) return false;

        $contains_only_valid_types
            = Enum\is_true_for_all_elements(
                $value[0], fn($type) => in_array($type, $valid_types));
        if (!$contains_only_valid_types) return false;
    }

    return true;
}

/**
 * Identity function.
 *
 * @template T
 * @return Closure(T): T
 */
function identity(): Closure
{
    return
        /**
         * @param mixed $value
         * @psalm-param T $value
         * @return mixed
         * @psalm-return T
         */
        static function ($value) {
            return $value;
        };
}

/**
 * Is a unary function which evaluates to $value for all inputs.
 *
 * @template T
 * @param mixed $value
 * @psalm-param T $value
 * @return Closure(): T
 */
function always($value): Closure
{
    return
        /**
         * @return mixed
         * @psalm-return T
         */
        static function () use ($value) {
            return $value;
        };
}

/**
 * Returns TRUE if $left is equal to $right and they are of the same type.
 *
 * @param mixed $right
 *
 * @return Closure(mixed): bool
 */
function equal($right): Closure
{
    return
        /**
         * @param mixed $left
         * @return bool
         */
        static function ($left) use ($right) {
            return $left === $right;
        };
}

/**
 * Returns TRUE if $left is strictly less than $right.
 *
 * @param mixed $right
 *
 * @return Closure(mixed): bool
 */
function less_than($right): Closure
{
    return
        /**
         * @param mixed $left
         * @return bool
         */
        static function ($left) use ($right) {
            return $left < $right;
        };
}

/**
 * Returns TRUE if $left is strictly greater than $right.
 *
 * @param mixed $right
 *
 * @return Closure(mixed): bool
 */
function greater_than($right): Closure
{
    return
        /**
         * @param mixed $left
         * @return bool
         */
        static function ($left) use ($right) {
            return $left > $right;
        };
}

/**
 * It allows for conditional execution of code fragments.
 *
 * @template I
 * @template O
 * @param callable(I):bool $cond
 * @return Closure(callable(I):O):((\Closure(callable(I):O):\Closure(I):O)
 */
function if_else(callable $cond): Closure
{
    return
        /**
         * @param callable(I):O $then
         * @return Closure(callable(I):O):(Closure(I):O)
         */
        static function (callable $then) use ($cond): Closure {
            return
                /**
                 * @param callable(I):O $else
                 * @return Closure(I):O
                 */
                static function (callable $else) use ($cond, $then): Closure {
                    return
                        /**
                         * @param mixed $value
                         * @psalm-param I $value
                         * @return mixed
                         * @psalm-return O
                         */
                        static function ($value) use ($cond, $then, $else) {
                            return $cond($value) ? $then($value) : $else($value);
                        };
                };
        };
}

/**
 * Pattern-Matching Semantics.
 *
 * @template I
 * @template O
 * @param array{callable(I):bool, callable(I):O}[] $matches
 * @param callable(I):O $exhaust
 * @return Closure(I):O
 */
function matching(array $matches, callable $exhaust): Closure
{
    return
        /**
         * @param mixed $value
         * @psalm-param I $value
         * @return mixed
         * @psalm-return O
         */
        static function ($value) use ($matches, $exhaust) {
            foreach ($matches as [$predicate, $callback]) {
                if ($predicate($value)) {
                    return $callback($value);
                }
            }

            return $exhaust($value);
        };
}

/**
 * Determines whether any returns of $functions is true-ish.
 *
 * @param iterable<callable> $functions
 *
 * @return Closure(mixed): bool
 */
function any(iterable $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($functions): bool {
            foreach ($functions as $function) {
                if ($function($value)) {
                    return true;
                }
            }

            return false;
        };
}

/**
 * Determines whether all returns of $functions are true-ish.
 *
 * @param iterable<callable> $functions
 *
 * @return Closure(mixed): bool
 */
function all(iterable $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($functions): bool {
            foreach ($functions as $function) {
                if (!$function($value)) {
                    return false;
                }
            }

            return true;
        };
}

/**
 * Boolean "not".
 *
 * @param callable $function
 *
 * @return Closure(mixed): bool
 */
function not(callable $function): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($function): bool {
            return !$function($value);
        };
}

/**
 * Function composition is the act of pipelining the result of one function,
 * to the input of another, creating an entirely new function.
 *
 * @param array<callable> $functions
 *
 * @return Closure(mixed): mixed
 */
function compose(array $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return mixed
         */
        static function ($value) use ($functions) {
            return array_reduce(
                array_reverse($functions),
                /**
                 * @param mixed $value
                 * @param callable $function
                 * @return mixed
                 */
                static function ($value, $function) {
                    return $function($value);
                },
                $value
            );
        };
}

/**
 * Converts the given $value to a boolean.
 *
 * @return Closure(mixed): bool
 */
function bool(): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value): bool {
            return (bool)$value;
        };
}

/**
 * In computer science, a NOP or NOOP (short for No Operation) is an assembly language instruction,
 * programming language statement, or computer protocol command that does nothing.
 *
 * @return Closure(): void
 */
function noop(): Closure
{
    return static function (): void {
    };
}

/**
 * Holds a function for lazily call.
 *
 * @param callable $function
 *
 * @return Closure(): mixed
 */
function hold(callable $function): Closure
{
    return
        /**
         * @return mixed
         */
        static function () use ($function) {
            return call_user_func_array($function, array_values(func_get_args()));
        };
}

/**
 * Lazy echo.
 *
 * @param string $value
 *
 * @return Closure(): void
 */
function puts($value): Closure
{
    return static function () use ($value): void {
        echo $value;
    };
}

/**
 * Partial application.
 *
 * @param callable $callable
 * @param mixed ...$partial
 *
 * @return Closure(mixed[]): mixed
 */
function partial(callable $callable, ...$partial): Closure
{
    return
        /**
         * @param mixed[] $args
         * @return mixed
         */
        static function (...$args) use ($callable, $partial) {
            return call_user_func_array($callable, array_merge($partial, $args));
        };
}

/**
 * Calls a function if the predicate is true.
 *
 * @template T
 * @param callable $predicate
 * @return Closure(callable():T):(T|null)
 */
function if_then(callable $predicate): Closure
{
    return function (callable $then) use ($predicate) {
        if ($predicate()) {
            return $then();
        }

        return null;
    };
}

/**
 * A lazy empty evaluation.
 *
 * @param mixed $var
 *
 * @return Closure():bool
 */
function is_empty($var): Closure
{
    return static function () use ($var): bool {
        return empty($var);
    };
}

/**
 * A lazy is_null evaluation.
 *
 * @param mixed $var
 *
 * @return Closure():bool
 */
function isnull($var): Closure
{
    return static function () use ($var): bool {
        return $var === null;
    };
}

/**
 * Lazily evaluate a function.
 *
 * @template T
 * @param callable(...mixed): T $callable
 * @param array $args
 * @return Closure(): T
 */
function lazy(callable $callable, ...$args): Closure
{
    return
        /**
         * @return mixed
         * @psalm-return T
         */
        static function () use ($callable, $args) {
            /** @psalm-suppress MixedArgument */
            return call($callable, ...$args);
        };
}

/**
 * A call_user_func alias.
 *
 * @template T
 * @param callable(mixed...): T $callable
 * @param array $args
 * @return mixed
 * @psalm-return T
 */
function call(callable $callable, ...$args)
{
    /** @psalm-var T */
    return call_user_func_array($callable, $args);
}

/**
 * Pipes functions calls.
 *
 * @param callable[] $callbacks
 * @return Closure
 */
function pipe(array $callbacks): Closure
{
    return
        /**
         * @param mixed|null $initial
         * @return mixed
         */
        static function ($initial = null) use ($callbacks) {
            return array_reduce(
                $callbacks,
                /**
                 * @param mixed $result
                 * @param callable $callback
                 * @return mixed
                 */
                static function ($result, callable $callback) {
                    return $callback($result);
                },
                $initial
            );
        };
}

/**
 * Pipes callbacks until null is reached,
 * it returns the last non-null value
 *
 * @param callable[] $callbacks
 * @return Closure
 */
function conduit(array $callbacks): Closure
{
    return
        /**
         * @param mixed|null $initial
         * @return mixed
         */
        static function ($initial = null) use ($callbacks) {
            /** @var mixed $value */
            $value = $initial;
            /** @var mixed $last */
            $last = $value;

            foreach ($callbacks as $callback) {
                /** @var mixed $value */
                $value = $callback($value);

                if ($value === null) {
                    return $last;
                }

                /** @var mixed $last */
                $last = $value;
            }

            return $last;
        };
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

function is_maybe_tuple($x): bool {
    return
        is_array($x)
        && count($x) === 2
        && ($x[0] === ':error' || $x[0] === ':ok');
}

function lis_maybe_tuple(): Closure {
    return fn($x) => is_maybe_tuple($x);
}

/* bind_error :: callable -> Maybe any -> any */
function bind_error(callable $f, $maybe) {
    $maybe_tuple = is_maybe_tuple($maybe) ? $maybe : [ ':ok', $maybe ];

    [ $status, $value ] = $maybe_tuple;
    if ($status === ':error') return $maybe_tuple;
    return $f($value);
}

/* lbind_error
 * :: callable -> (callable -> [ status :: string, result :: any ] -> any) */
function lbind_error(callable $f): Closure {
    return fn($maybe) => bind_error($f, $maybe);
}

/* is_null_unset_or_empty :: any -> bool */
function is_null_unset_or_empty($x): bool {
    return ($x === null || empty($x) || !isset($x));
}

/* lis_null_unset_or_empty :: () -> (any -> bool) */
function lis_null_unset_or_empty(): Closure {
    return fn($x) => is_null_unset_or_empty($x);
}

function bound_val($x, $lower_bound, $upper_bound): Closure {
    return pipe([
        lmax($lower_bound),
        lmin($upper_bound)
    ])($x);
}

function lbound_val($lower_bound, $upper_bound): Closure {
    return fn($x) => bound_val($x, $lower_bound, $upper_bound);
}

function lmax(...$args): Closure {
    return fn($x) => max(Enum\flatten([$x, $args]));
}

function lmin(...$args): Closure {
    return fn($x) => min(Enum\flatten([$x, $args]));
}
