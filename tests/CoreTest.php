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

    public function testDefStruct() {
        c\defstruct(
            'User',
            [ 'name' => [['string']], 'age' => [['string', 'int', 'float']] ]);
        $our_user = ['name' => 'me', 'age' => 65];
        $bad_user = ['name' => 'me', 'age' => false];
        $this->assertTrue(c\is_type('User', $our_user));
        $this->assertFalse(c\is_type('User', $bad_user));

        c\defstruct('Account', [ 'id' => [['int'], 2], 'user' => [['User']] ]);
        $our_account = [ 'id' => 1, 'user' => $our_user ];
        $bad_account = [ 'id' => 1, 'user' => $bad_user ];
        $this->assertTrue(c\is_type('Account', $our_account));
        $this->assertFalse(c\is_type('Account', $bad_account));

        $built_user = c\mk('Account', ['user' =>  $our_user]);
        $this->assertEquals(['id' => 2, 'user' => $our_user], $built_user);
    }

    public function testIsType() {
        $this->assertTrue(c\is_type('string', '1'));
        $this->assertFalse(c\is_type('int', '1'));

        $this->assertTrue(c\is_type('array:int', [1, 2, 3]));
        $this->assertFalse(c\is_type('array:string', [1, 2, 3]));
    }
}

