# Phprelude

The standard library I wish PHP always had.

Significant credit belongs to [leocavalcante/Siler](https://github.com/leocavalcante/Siler), whose `Functional` library
has been largely transplanted into this project, and who, in addition, has made
programming in PHP bearable for me and other FP zealots.

## Usage

You can conveniently require all files in the library by using the
`autoload.php` file a la

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

## Structs!? In my PHP!?

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

p\defstruct(
    'User',
    [ 'id' => [['int', 'string'], 0]
    , 'name' => [['string']]
    ]);

$user0 = p\mk('User', ['name' => 'John']);
$user1 = p\mk('User', ['id' => 1, 'name' => 'Mike']);

echo json_encode($user0) . "\n"; // {"id":0,"name":"John"}
echo json_encode($user1) . "\n"; // {"id":1,"name":"Mike"}
```

## Wants and Desires

- [ ] Type aliases, i.e. non-struct type definitions, ex: `Num = ['int','float']`
- [ ] Add ability to use guard clauses with type definitions, for example, some
      way to write `BigNat = int > 1000` (Type "BigNat" corresponds to an int
      with a value greater than 1000)
- [ ] Clean up overall implementation of structs+types
- [ ] Add a "strict" version of type-checking for structs, which *doesn't* accept
      extra fields that aren't in the initial struct
- [ ] Make this library usable as a composer package, in addition to the current
      method of use
