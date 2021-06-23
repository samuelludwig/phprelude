<?php declare(strict_types=1); namespace Phprelude\Test\Core;
require_once __DIR__ . '/../src/core.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Core as c;

class CoreTest extends TestCase {


    public function testPartial()
    {
        $add = function (int $a, int $b) {
            return $a + $b;
        };

        $add1 = c\partial($add, 1);
        $this->assertSame(2, $add1(1));

        $commaExplode = c\partial('explode', ',');
        $this->assertSame(['foo', 'bar'], $commaExplode('foo,bar'));
    }

    public function testIfThen()
    {
        $this->expectOutputString('if_then');
        c\if_then(c\always(true))(c\puts('if_then'));
    }

    public function testIsEmpty()
    {
        $this->assertTrue(c\is_empty([])());
        $this->assertFalse(c\is_empty('[]')());
    }

    public function testIsNull()
    {
        $this->assertTrue(c\isnull(null)());
        $this->assertFalse(c\isnull([])());
    }

    public function testConcat()
    {
        $concat = c\concat('|');

        $this->assertSame('foo|bar', $concat('foo', 'bar'));
        $this->assertSame('foo', $concat('foo', false));
        $this->assertSame('foo', $concat('foo', null));
    }

    public function testLazy()
    {
        $will_trim = c\lazy('trim', ' foo ');
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

        $this->assertSame([2, 4, 6], c\map([1, 2, 3], $double));
        $this->assertSame([2, 4, 6], c\map($iterator(), $double));
        $this->assertSame([0, 1, 2], c\map(range(1, 3), $keys));
    }

    public function testLmap()
    {
        $double = c\lmap(function (int $i): int {
            return $i * 2;
        });

        $this->assertSame([2, 4, 6], $double([1, 2, 3]));
    }

    public function testPipe()
    {
        $pipe = c\pipe([
            c\add(1),
            c\add(1),
            c\add(1),
        ]);

        $this->assertSame(3, $pipe(0));
    }

    public function testConduit()
    {
        $this->assertSame('foo', c\conduit([c\always(null)])('foo'));

        $this->assertSame(
            'foobar',
            c\conduit([
                c\lconcat()('bar')
            ])('foo')
        );
    }

    public function testLazyJoin()
    {
        $pieces = ['foo', 'bar', 'baz'];

        $this->assertSame('foobarbaz', c\ljoin()($pieces));
        $this->assertSame('foo,bar,baz', c\ljoin(',')($pieces));
    }

    public function testFilter()
    {
        $input = ['foo', 'bar', 'baz'];

        $this->assertSame(['foo'], c\filter($input, function (string $value): bool {
            return $value === 'foo';
        }));
    }

    public function testLazyFilter()
    {
        $input = [1, 2, 3, 4];

        $even = function (int $n): bool {
            return ($n % 2) === 0;
        };

        $this->assertSame([2, 4], c\lfilter($even)($input));
    }

    public function testEvenOdd()
    {
        $this->assertTrue(c\even(2));
        $this->assertFalse(c\odd(2));
        $this->assertFalse(c\even(1));
        $this->assertTrue(c\odd(1));
    }

    public function testFind()
    {
        $list = [1, 2, 3];
        $this->assertSame(2, c\find($list, c\even));

        $fst_even = c\lfind(c\even);
        $this->assertSame(2, $fst_even($list));

        $this->assertSame(0, c\find($list, c\equal(0), 0));
    }

    public function testSort()
    {
        $list = [1, 2, 3];

        $desc = function (int $a, int $b): int {
            return $b <=> $a;
        };

        $this->assertSame([3, 2, 1], c\sort($list, $desc));

        $sort_desc = c\lsort($desc);
        $this->assertSame([3, 2, 1], $sort_desc($list));

        $this->assertSame($list, $list);
    }

    public function testFirst()
    {
        $desc = function (int $a, int $b): int {
            return $b <=> $a;
        };

        $list = [];
        $this->assertNull(c\first($list, $desc));
        $this->assertSame(42, c\first($list, $desc, 42));

        $list = [1, 2, 3];
        $this->assertSame(3, c\first($list, $desc));

        $higher = c\lfirst($desc);
        $this->assertSame(3, $higher($list));
    }

    public function testSum()
    {
        $this->assertSame(2, c\sum(1, 1));
    }

    public function testFold()
    {
        $this->assertSame(6, c\fold([1, 2, 3], 0, c\sum));
    }
}
