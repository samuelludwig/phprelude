<?php declare(strict_types=1); namespace Phprelude\Environment;
use Closure;

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
