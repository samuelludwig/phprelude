<?php declare(strict_types=1);

namespace Siler\Container;

use OverflowException;
use UnderflowException;
use function Siler\array_get;
use function Siler\Functional\call;

/**
 * Get a value from the container.
 *
 * @template T
 * @param string $key The key to be searched on the container
 * @param mixed $default Default value when the key does not exists on the container
 * @param array $args If the given value is callable it will be automatically called with this arguments
 * @param bool $reusable Whether value should be called every time or just once
 * @psalm-param T $default Default value when the key does not exists on the container
 * @psalm-return T
 */
function get(string $key, $default = null, array $args = [], bool $reusable = true)
{
    $container = Container::getInstance();
    /** @psalm-var T|callable(mixed...):T $value */
    $value = array_get($container->values, $key, $default);

    if (is_callable($value)) {
        /** @var callable(mixed...):T $callable_value */
        $callable_value = $value;
        $value = call($callable_value, ...$args);
        if ($reusable) {
            set($key, $value);
        }
    }

    return $value;
}

/**
 * Set a value in the container.
 *
 * @param string $key Identified by the given key
 * @param mixed $value The value to be stored
 */
function set(string $key, $value): void
{
    $container = Container::getInstance();
    $container->values[$key] = $value;
}

/**
 * Checks if there is some value in the given $key.
 *
 * @param string $key Key to search in the Container.
 * @return bool
 */
function has(string $key): bool
{
    $container = Container::getInstance();

    return array_key_exists($key, $container->values);
}

/**
 * Clears the value on the container.
 *
 * @param string $key
 */
function clear(string $key): void
{
    $container = Container::getInstance();
    unset($container->values[$key]);
}

/**
 * Sugar for Container\set that throws an OverflowException when the key is already in use.
 * Useful for dependency injection.
 *
 * @param string $serviceName
 * @param mixed $service
 */
function inject(string $serviceName, $service): void
{
    $container = Container::getInstance();

    if (array_key_exists($serviceName, $container->values)) {
        throw new OverflowException("$serviceName already in use");
    }

    $container->values[$serviceName] = $service;
}

/**
 * Sugar for Container\get that throws an UnderflowException when the key isn't initialized.
 * Useful for dependency injection/IoC.
 *
 * @template T
 * @param string $serviceName
 * @param bool $reusable
 * @return mixed
 * @psalm-return T
 */
function retrieve(string $serviceName, bool $reusable = true)
{
    $container = Container::getInstance();

    if (!array_key_exists($serviceName, $container->values)) {
        throw new UnderflowException("$serviceName not initialized");
    }

    /** @psalm-var T|callable(mixed...):T $service */
    $service = $container->values[$serviceName];

    if (is_callable($service)) {
        /** @var callable(mixed...):T $callable_service */
        $callable_service = $service;
        $service = call($callable_service);
        if ($reusable) {
            set($serviceName, $service);
        }
    }

    return $service;
}

/**
 * @internal Class Container
 * @package Siler\Container
 */
final class Container
{
    /** @var array<string, mixed> */
    public $values = [];

    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        /** @var Container|null */
        static $instance = null;

        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }
}
