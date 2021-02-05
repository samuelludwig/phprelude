<?php declare(strict_types=1);

namespace Siler\Test\Unit\Functional;

use Iterator;
use PHPUnit\Framework\TestCase;
use Siler\Functional as f;

class FunctionalTest extends TestCase
{
    public function testId()
    {
        $this->assertSame('foo', f\identity()('foo'));
    }

    public function testAlways()
    {
        $this->assertSame('foo', f\always('foo')());
        $this->assertSame('foo', f\always('foo')('bar'));
    }

    public function testEq()
    {
        $this->assertTrue(f\equal('foo')('foo'));
        $this->assertTrue(f\equal(1)(1));
        $this->assertFalse(f\equal(1)('1'));
        $this->assertFalse(f\equal(1)(true));
    }

    public function testLt()
    {
        $this->assertTrue(f\less_than(2)(1));
        $this->assertFalse(f\less_than(2)(2));
        $this->assertFalse(f\less_than(2)(3));
    }

    public function testGt()
    {
        $this->assertFalse(f\greater_than(2)(1));
        $this->assertFalse(f\greater_than(2)(2));
        $this->assertTrue(f\greater_than(2)(3));
    }

    public function testIfe()
    {
        $foo = f\if_else(f\identity())(f\always('foo'))(f\always('bar'));
        $this->assertSame('foo', $foo(true));
    }

    public function testMatch()
    {
        $test = f\matching([
            [f\equal('foo'), f\always('bar')],
            [f\equal('bar'), f\always('baz')],
            [f\equal('baz'), f\always('qux')],
        ], f\always('foobar'));

        $this->assertSame('bar', $test('foo'));
        $this->assertSame('baz', $test('bar'));
        $this->assertSame('qux', $test('baz'));
        $this->assertSame('foobar', $test('qux'));
    }

    public function testAny()
    {
        $test = f\any([f\equal(2), f\greater_than(2)]);

        $this->assertFalse($test(1));
        $this->assertTrue($test(2));
        $this->assertTrue($test(3));
    }

    public function testAll()
    {
        $this->assertTrue(f\all([f\less_than(2), f\less_than(3)])(1));
        $this->assertFalse(f\all([f\equal(1), f\greater_than(1)])(1));
    }

    public function testNot()
    {
        $this->assertTrue(f\not(f\equal(2))(3));
        $this->assertFalse(f\not(f\equal(2))(2));
    }

    public function testMath()
    {
        $this->assertSame(2, f\add(1)(1));
        $this->assertSame(1, f\sub(2)(3));
        $this->assertSame(4, f\mul(2)(2));
        $this->assertSame(2, f\div(2)(4));
        $this->assertSame(-1, f\sub(3)(2));
        $this->assertSame(0.5, f\div(4)(2));
        $this->assertSame(2, f\mod(3)(5));
        $this->assertSame(2, f\mod(-3)(5));
        $this->assertSame(-2, f\mod(3)(-5));
        $this->assertSame(-2, f\mod(-3)(-5));
    }

    public function testCompose()
    {
        $test = f\compose([f\add(2), f\mul(2)]);
        $this->assertSame(6, $test(2));

        $test = f\compose([f\div(2), f\sub(1)]);
        $this->assertSame(0.5, $test(2));
    }

    public function testBool()
    {
        $this->assertTrue(f\bool()(true));
        $this->assertTrue(f\bool()('foo'));
        $this->assertTrue(f\bool()(1));

        $this->assertFalse(f\bool()(false));
        $this->assertFalse(f\bool()(''));
        $this->assertFalse(f\bool()(0));
    }

    public function testNoop()
    {
        f\noop()();
        $this->assertTrue(true);
    }

    public function testHold()
    {
        $this->expectOutputString('foo');

        $echoFoo = function ($val) {
            echo $val;
        };

        f\if_else(f\bool())(f\hold($echoFoo))(f\noop())('foo');
    }

    public function testPuts()
    {
        $this->expectOutputString('foo');
        f\puts('foo')();
    }

    public function testFlatten()
    {
        $input = [0, 1, [2, 3], [4, 5], [6, [7, 8, [9]]]];
        $expected = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $actual = f\flatten($input);

        $this->assertSame($expected, $actual);
    }

    public function testHead()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = 1;
        $actual = f\head($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
        $this->assertSame('foo', f\head(['foo', 'bar', 'baz']));
        $this->assertNull(f\head([]));
        $this->assertSame('foo', f\head([], 'foo'));
    }

    public function testLast()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = 5;
        $actual = f\last($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
        $this->assertSame('baz', f\last(['foo', 'bar', 'baz']));
        $this->assertNull(f\last([]));
        $this->assertSame('foo', f\last([], 'foo'));
    }

    public function testTail()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [2, 3, 4, 5];
        $actual = f\tail($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testInit()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [1, 2, 3, 4];
        $actual = f\init($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testUncons()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [1, [2, 3, 4, 5]];

        list($head, $tail) = f\uncons($input);
        $actual = [$head, $tail];

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testNonNull()
    {
        $input = [0, null, false, '', null];
        $this->assertSame([0, false, ''], f\non_null($input));
    }

    public function testNonEmpty()
    {
        $input = [0, 1, false, true, '', 'foo', null, [], ['bar']];
        $this->assertSame([1, true, 'foo', ['bar']], f\non_empty($input));
    }

    public function testPartial()
    {
        $add = function (int $a, int $b) {
            return $a + $b;
        };

        $add1 = f\partial($add, 1);
        $this->assertSame(2, $add1(1));

        $commaExplode = f\partial('explode', ',');
        $this->assertSame(['foo', 'bar'], $commaExplode('foo,bar'));
    }

    public function testIfThen()
    {
        $this->expectOutputString('if_then');
        f\if_then(f\always(true))(f\puts('if_then'));
    }

    public function testIsEmpty()
    {
        $this->assertTrue(f\is_empty([])());
        $this->assertFalse(f\is_empty('[]')());
    }

    public function testIsNull()
    {
        $this->assertTrue(f\isnull(null)());
        $this->assertFalse(f\isnull([])());
    }

    public function testConcat()
    {
        $concat = f\concat('|');

        $this->assertSame('foo|bar', $concat('foo', 'bar'));
        $this->assertSame('foo', $concat('foo', false));
        $this->assertSame('foo', $concat('foo', null));
    }

    public function testLazy()
    {
        $will_trim = f\lazy('trim', ' foo ');
        $this->assertSame('foo', $will_trim());
    }

    public function testMap()
    {
        $double = function (int $i): int {
            return $i * 2;
        };

        $iterator = function (): Iterator {
            $i = 1;

            while ($i <= 3) {
                yield $i++;
            }
        };

        $keys = function (int $_, int $key): int {
            return $key;
        };

        $this->assertSame([2, 4, 6], f\map([1, 2, 3], $double));
        $this->assertSame([2, 4, 6], f\map($iterator(), $double));
        $this->assertSame([0, 1, 2], f\map(range(1, 3), $keys));
    }

    public function testLmap()
    {
        $double = f\lmap(function (int $i): int {
            return $i * 2;
        });

        $this->assertSame([2, 4, 6], $double([1, 2, 3]));
    }

    public function testPipe()
    {
        $pipe = f\pipe([
            f\add(1),
            f\add(1),
            f\add(1),
        ]);

        $this->assertSame(3, $pipe(0));
    }

    public function testConduit()
    {
        $this->assertSame('foo', f\conduit([f\always(null)])('foo'));

        $this->assertSame(
            'foobar',
            f\conduit([
                f\lconcat()('bar')
            ])('foo')
        );
    }

    public function testLazyJoin()
    {
        $pieces = ['foo', 'bar', 'baz'];

        $this->assertSame('foobarbaz', f\ljoin()($pieces));
        $this->assertSame('foo,bar,baz', f\ljoin(',')($pieces));
    }

    public function testFilter()
    {
        $input = ['foo', 'bar', 'baz'];

        $this->assertSame(['foo'], f\filter($input, function (string $value): bool {
            return $value === 'foo';
        }));
    }

    public function testLazyFilter()
    {
        $input = [1, 2, 3, 4];

        $even = function (int $n): bool {
            return ($n % 2) === 0;
        };

        $this->assertSame([2, 4], f\lfilter($even)($input));
    }

    public function testEvenOdd()
    {
        $this->assertTrue(f\even(2));
        $this->assertFalse(f\odd(2));
        $this->assertFalse(f\even(1));
        $this->assertTrue(f\odd(1));
    }

    public function testFind()
    {
        $list = [1, 2, 3];
        $this->assertSame(2, f\find($list, f\even));

        $fst_even = f\lfind(f\even);
        $this->assertSame(2, $fst_even($list));

        $this->assertSame(0, f\find($list, f\equal(0), 0));
    }

    public function testSort()
    {
        $list = [1, 2, 3];

        $desc = function (int $a, int $b): int {
            return $b <=> $a;
        };

        $this->assertSame([3, 2, 1], f\sort($list, $desc));

        $sort_desc = f\lsort($desc);
        $this->assertSame([3, 2, 1], $sort_desc($list));

        $this->assertSame($list, $list);
    }

    public function testFirst()
    {
        $desc = function (int $a, int $b): int {
            return $b <=> $a;
        };

        $list = [];
        $this->assertNull(f\first($list, $desc));
        $this->assertSame(42, f\first($list, $desc, 42));

        $list = [1, 2, 3];
        $this->assertSame(3, f\first($list, $desc));

        $higher = f\lfirst($desc);
        $this->assertSame(3, $higher($list));
    }

    public function testSum()
    {
        $this->assertSame(2, f\sum(1, 1));
    }

    public function testFold()
    {
        $this->assertSame(6, f\fold([1, 2, 3], 0, f\sum));
    }
}
