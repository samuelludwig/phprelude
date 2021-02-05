<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL;

use DateTime;
use GraphQL\Error\Error;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;
use Siler\GraphQL\DateScalar;
use Siler\GraphQL\DateTimeScalar;

class DateTimeScalarTest extends TestCase
{
    public function testDateSerialize(): void
    {
        $expected = '2020-07-18';
        $ds = new DateScalar();

        $actual = $ds->serialize(DateTime::createFromFormat(DateScalar::FORMAT, $expected));
        self::assertSame($expected, $actual);

        $this->expectException(Error::class);
        $ds->serialize($expected);
    }

    public function testDateParse(): void
    {
        $value = '2020-07-18';
        $literal = new StringValueNode(['value' => $value]);

        $expected = DateTime::createFromFormat(DateScalar::FORMAT, $value);
        $ds = new DateScalar();

        $actual = $ds->parseLiteral($literal);
        self::assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $ds->parseLiteral(new IntValueNode(['value' => 0]));

        $actual = $ds->parseValue($value);
        self::assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $ds->parseValue('2020-07-18');
    }

    public function testDateTimeSerialize(): void
    {
        $expected = '2020-07-18 13:40:00';
        $dts = new DateTimeScalar();

        $actual = $dts->serialize(DateTime::createFromFormat(DateTimeScalar::FORMAT, $expected));
        self::assertSame($expected, $actual);

        $this->expectException(Error::class);
        $dts->serialize($expected);
    }

    public function testDateTimeParse(): void
    {
        $value = '2020-07-18 13:40:00';
        $literal = new StringValueNode(['value' => $value]);

        $expected = DateTime::createFromFormat(DateTimeScalar::FORMAT, $value);
        $dts = new DateTimeScalar();

        $actual = $dts->parseLiteral($literal);
        self::assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $dts->parseLiteral(new IntValueNode(['value' => 0]));

        $actual = $dts->parseValue($value);
        self::assertEquals($expected, $actual);

        $this->expectException(Error::class);
        $dts->parseValue('2020-07-18');
    }
}
