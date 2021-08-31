<?php declare(strict_types=1); namespace Phprelude\Optics\Lens;
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
