<?php declare(strict_types=1); namespace Phprelude;

/* Proceed only if there are no function collisions TODO: refine */
if (!function_exists('\Phprelude\Core\require_directory')) {
    require_once __DIR__ . '/src/core.php';
    \Phprelude\Core\require_directory(__DIR__ . '/src/optics');
    \Phprelude\Core\require_directory(__DIR__ . '/src');
}
