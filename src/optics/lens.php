<?php declare(strict_types=1); namespace Phprelude\Optics\Lens;
require_once __DIR__ . '/../enum.php';
use \Phprelude\Enum;
use Closure;

function mk_setter($name): Closure {
    return function ($source, $val) use ($name) {
        $source[$name] = $val;
        return $source;
    };
}

function mk_getter($name): Closure {
    return fn($source) => $source[$name];
}

function mk_lens($name) {
    return lens(mk_getter($name), mk_setter($name));
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
    /* Composite is broader then successive iterations */
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

