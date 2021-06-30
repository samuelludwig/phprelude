<?php declare(strict_types=1); namespace Phprelude;
require_once __DIR__ . '/src/core.php';

/* Proceed only if there are no function collisions TODO: refine */
if (!function_exists('identity')) {
    \Phprelude\Core\require_directory(__DIR__ . '/src');
}
