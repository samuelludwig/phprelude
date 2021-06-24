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

    public function testPipe()
    {
        $pipe = c\pipe([
            fn($x) => ($x + 1),
            fn($x) => ($x + 1),
            fn($x) => ($x + 1),
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
}
