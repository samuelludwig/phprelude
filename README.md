# Phprelude

The standard library I wish PHP always had.

Significant credit belongs to [leocavalcante/Siler](https://github.com/leocavalcante/Siler), whose `Functional` library
has been largely transplanted into this project, and who, in addition, has made
programming in PHP bearable for me and other FP zealots.

## Usage

### Composer

Install via

```bash
composer install "samuelludwig/phprelude"
```

And then autoload the library namespaces via the typical

```php
require_once __DIR__ . '/path/to/vendor/autoload.php';
```

### Manual Install + Require

If you opt to add the library as a submodule to your project, you can conveniently
require all files in the library by using the `autoload.php` file a la

```php
require_once __DIR__ . '/path/to/Phprelude/autoload.php';
```

## Modules

The modules breakdown as follows:
  - Core: Suitable primatives for FP tools
  - Enum: Functions for processing and manipulating "Enumerables" (Arrays)
  - Io: Functions for I/O
  - Math: Math-related functions
  - Natural: Integer-related functions
  - Str: String-related functions
  - File: File-related functions
  - Environment: Environment-variable-related functions
  - Json: JSON-related functions

## Notable Features

### Piping!

Users of Elixirs wonderful `|>` operator or the threading macro `->` found in
most lisps should be very familar with the concept of this function.

Graciously taken verbatim from Siler is the indespensible `pipe`. Few things
make my eyes glaze over as much as nested function calls and intermediate
variables: this is where `Core\pipe` comes to the rescue!

We can supply `pipe` with a list of callbacks which each expect one argument,
and as a result we will receive a lambda, which will feed the output of each
callback into the next one in the list, and finally returning the output of the
last callback.

Here's an example:

```php
<?php declare(strict_types=1); namespace StructTest;
require_once __DIR__ . '\path\to\Phprelude\autoload.php';
use \Phprelude\Core as p;
use Closure;

function add2($n) {
  return $n+2;
}

function ladd2(): Closure {
  return fn($x) => add2($x);
}

$double = fn($n) => $n*2;
$triple = fn($n) => $n*3;

echo p\pipe([
  $triple,
  $double,
  fn($x) => add2($x),
  ladd2(),
])(4); // (4 * 3 * 2) + 2 + 2 ==> 28
```

Pipelines are a blessing when it comes to data-transformation/processing, never
need to create an intermediate variable ever again!

Just about every function in this library should have an accompanying
lambda-ized version (oft named `l<original-funcs-name>`) to make piping more
immediately convienient.

### Extraction and Manipulation

#### Extracting values into a given format

The `Enum` module houses a myriad of tools dedicated to manipulating data housed
in arrays.

One of my personal highest-milage functions when it comes to picking out and
molding data is easily `Enum\extract_values_into_format`.

This function accepts two values, a source array, to extract from, and a
mapping of new key-names to keys in the source array. A map of `new-key` to
`source-key` may also involve a transformation of `source-key`'s value; in
which case, `source-key` will be given as an array, the first element being
the key name, and the second being the callable to apply to the value.

So,

```php
<?php declare(strict_types=1); namespace StructTest;
require_once __DIR__ . '\path\to\Phprelude\autoload.php';
use \Phprelude\Enum;
use Closure;

// Say I have an array like
$a = [ 'pets_name' => 'mark', 'pet_kind' => 'dog' ];
// and I have some function thats expecting the keys to be named differently, like
// "`animal_name`" instead of "`pet_name`" and "`species`" instead of "`pet_kind`",
// and maybe its also expecting the `species` to always be capitalized, I could
// then write

$new_a =
  Enum\extract_values_into_format(
    $a,
    [ 'animal_name' => 'pets_name'
    , 'species' => ['pet_kind', fn($x) => strtoupper($x)]
    ]);

// Et voila, we have our new array
echo $new_a == [ 'animal_name' => 'mark', 'species' => 'DOG' ]; // 1
```

A similar, more narrowly-focused version of this function exists called
`Enum\extract_values`, which will merely return list of the requested values. My
main use case for this function is destructuring some select fields I care about
explicitly:

```php
<?php declare(strict_types=1); namespace StructTest;
require_once __DIR__ . '\path\to\Phprelude\autoload.php';
use \Phprelude\Enum;
use Closure;

// Given:
$a = [ 'first' => 'door', 'second' => 'cat', 'third' => 'orange' ];

// We can extract specific values into a list via:
[ $x, $y ] = Enum\extract_values($a, ['third', 'first']);

echo "$x $y"; // "orange door"
```

#### Comparing against contents of an array

TODO: `is_true_for_all/some_elements`

### Structs!? In my PHP!?

One of the most recent (read: unrefined) new additions to this library is the
abuse of PHP constants to create a naive implementation of structs, with a
pseudo-built-in general constructor function.

A struct is defined using the `Core\defstruct` function, which takes two
arguments:
  1. The name of the struct
  2. The "layout" of the struct

A struct's layout is an associative array, with each key being the name of a
given field, and each value being a one or two element array, where the first
element is either:
  1. Another nested struct layout
  2. A list of possible "types" the ultimate value can be, either the core types
     understood by PHP, or names of other structs

The second element, if there is one, will be the default value for a given field
if one was not provided in the constructor function.

When a struct is defined using `defstruct`, a constant, named using the provided
name of the struct, is created and set to the value of the structs layout. Note
that this means `defstruct` is *impure*, because you will be adding constants to
the global namespace!

After declaration, a struct can be constructed using the `Core\mk` function,
which takes the name of the struct, and an associative array. Fields with
defined defaults, that are not also present in the provided associative array,
are substituted with their given default values. The `mk` function will error if
the resulting array does not match the type of the struct.

NOTE: At the moment, a struct, after definition, can have a superset of the
fields in its layout, and still be considered the same "type".

An example struct definition (and subsequent construction) might look like:

```php
<?php declare(strict_types=1); namespace StructTest;
require_once __DIR__ . '\path\to\Phprelude\autoload.php';
use \Phprelude\Core as p;
use \Phprelude\Json;

// We can use const to pseudo-namespace our struct without too much hassle
const UserT = __NAMESPACE__ . '\User';
p\defstruct(
    UserT,
    [ 'id' => [['int', 'string'], 0]
    , 'name' => [['string']]
    ]);

$user0 = p\mk(UserT, ['name' => 'John']);
$user1 = p\mk(UserT, ['id' => 1, 'name' => 'Mike']);

echo Json\encode($user0) . "\n"; // {"id":0,"name":"John"}
echo Json\encode($user1) . "\n"; // {"id":1,"name":"Mike"}
```

## Wants and Desires

- [ ] Type aliases, i.e. non-struct type definitions, ex: `Num = ['int','float']`
- [ ] Add ability to use guard clauses with type definitions, for example, some
      way to write `BigNat = int > 1000` (Type "BigNat" corresponds to an int
      with a value greater than 1000)
- [ ] Clean up overall implementation of structs+types
- [ ] Add a "strict" version of type-checking for structs, which *doesn't* accept
      extra fields that aren't in the initial struct
