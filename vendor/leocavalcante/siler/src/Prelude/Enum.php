<?php declare(strict_types=1);

namespace Siler\Prelude;

use ReflectionClass;
use ReflectionException;
use UnexpectedValueException;

/**
 * Abstract class for enums.
 */
abstract class Enum
{
    /** @var array<string, array> */
    private static $constsMemo = [];

    /**
     * @param mixed $value
     * @return static
     * @throws ReflectionException
     */
    public static function of($value): self
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new static($value);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    protected static function consts(): array
    {
        $class_name = static::class;

        if (!\array_key_exists($class_name, self::$constsMemo)) {
            $reflection = new ReflectionClass($class_name);
            self::$constsMemo[$class_name] = $reflection->getConstants();
        }

        return self::$constsMemo[$class_name];
    }

    /**
     * @param mixed $value
     * @return bool
     * @throws ReflectionException
     */
    protected static function valid($value): bool
    {
        return \in_array($value, array_values(static::consts()), true);
    }

    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     * @throws ReflectionException
     */
    public function __construct($value)
    {
        if (!static::valid($value)) {
            $class_name = static::class;
            throw new UnexpectedValueException("Invalid value ($value) for enum ($class_name)");
        }

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function valueOf()
    {
        return $this->value;
    }

    /**
     * @param Enum $other
     * @return bool
     */
    public function sameAs(Enum $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
