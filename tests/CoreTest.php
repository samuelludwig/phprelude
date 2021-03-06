<?php declare(strict_types=1); namespace Phprelude\Test\Core;
require_once __DIR__ . '/../src/core.php';
require_once __DIR__ . '/../src/str.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Core as c;
use \Phprelude\Str;

class CoreTest extends TestCase {

    public function testBindError() {
        $f_no_err = fn($x) => [ ':ok', -$x ];
        $f_with_err = fn($x) => [ ':error', "Error with input: $x" ];

        $result = c\pipe([
            c\lbind_error($f_no_err),
            c\lbind_error($f_no_err),
        ])(1);
        $expected = [ ':ok', 1 ];
        $this->assertEquals($expected, $result);

        $result = c\pipe([
            c\lbind_error($f_no_err),
            c\lbind_error($f_with_err),
            c\lbind_error($f_no_err),
        ])(1);
        $expected = [ ':error', 'Error with input: -1' ];
        $this->assertEquals($expected, $result);
    }

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
                Str\lconcat()('bar')
            ])('foo')
        );
    }
}
