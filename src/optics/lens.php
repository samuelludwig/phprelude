<?php declare(strict_types=1); namespace Phprelude\Optics\Lens;
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/../enum.php';
use \Phprelude\Core as p;
use \Phprelude\Enum;
use Closure;

function mk_setter($name): Closure {
    return fn($source, $val) => Enum\set_key_val($source, $name, $val);
}

function mk_getter($name): Closure {
    return fn($source) => $source[$name];
}

function mk_lens($name): array {
    return lens(mk_getter($name), mk_setter($name));
}

/* Works for types, normal assoc arrays, and number-indexed arrays with unique
 * string values */
function mk_lenses_for(array $type): array {
    $type_keys = [];
    if (Enum\is_assoc($type)) {
        foreach ($type as $key => $_) { $type_keys[$key] = $key; }
    } else {
        foreach ($type as $key) { $type_keys[$key] = $key; }
    }
    return Enum\map($type_keys, fn($x) => mk_lens($x));
}

/* TODO: Devise a way to constrain a given lens to a given struct-type */
function lens(callable $getter, callable $setter): array {
    $modify = fn($source, $f) => $setter($source, $f($getter($source)));
    return [ 'get' => $getter , 'set' => $setter , 'modify' => $modify ];
}

function view($lens) {
    return fn($source) => $lens['get']($source);
}

function set($lens, $val) {
    return fn($source) => $lens['set']($source, $val);
}

function over($lens, callable $f) {
    return fn($source) => $lens['modify']($source, $f);
}

function compose(...$lenses) {
    /* Initial composite is broader then successive iterations */
    $composite = Enum\head($lenses);
    foreach (Enum\tail($lenses) as $lens) {
        $composite['get']
            = fn($source) =>
            view($lens)(view($composite)($source));

        /* Need to recursively update the datastructure and ultimately return it */
        $composite['set']
            = function ($source, $val) use ($lens, $composite) {
                // outer set is `composite['set']`
                // inner set is `lens['set']`
                $inner_set = set($lens, $val);
                return over($composite, $inner_set)($source);
            };

        $composite['modify']
            = function ($source, $f) use ($lens, $composite) {
                // outer modify is `composite['modify']`
                // inner modify is `lens['modify']`
                $inner_modify = over($lens, $f);
                return over($composite, $inner_modify)($source);
            };
    }
    return $composite;
}

/* Given two lists of lenses, create a list of new lenses which is comprised of
 * the combinatoric compositions of every lens in the original two lists.
 * Invalid lenses will be created... don't use them. */
function compose_lists($l1, $l2): array {
    $lenses = [];
    foreach ($l2 as $lens_name => $lens) {
        foreach ($l1 as $prefix_name => $prefix_lens) {
            $composed_name = implode('_', [$prefix_name, $lens_name]);
            $lenses[$composed_name] = compose($prefix_lens, $lens);
        }
    }

    return $lenses;
}

function compose_all(...$ls): array {
    [$head, $lens_array] = Enum\uncons(array_reverse($ls));
    $composition
        = Enum\fold(
            $lens_array, $head, fn($l1, $l2) => compose_lists($l2, $l1));

    return $composition;
    //$f = ;
}

/**
 * Invoke view for a list of lenses pertaining to a single source.
 */
function view_all(array $lenses): Closure {
    return fn($source) => Enum\map($lenses, fn($l) => view($l)($source));
}
