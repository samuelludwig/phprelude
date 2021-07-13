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
