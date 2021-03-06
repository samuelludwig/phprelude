<?php declare(strict_types=1); namespace Phprelude\Test\Str;
require_once __DIR__ . '/../src/str.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Str;

class StringTest extends TestCase {

    public function testConcat()
    {
        $concat = Str\concat('|');

        $this->assertSame('foo|bar', $concat('foo', 'bar'));
        $this->assertSame('foo', $concat('foo', false));
        $this->assertSame('foo', $concat('foo', null));
    }


    public function testSubstringExists() {
        $this->assertTrue(Str\substring_exists('dog', 'o'));
        $this->assertTrue(Str\substring_exists('dog', 'dog'));
        $this->assertFalse(Str\substring_exists('dog', 'c'));
        $this->assertFalse(Str\substring_exists('dog', 'cog'));
    }
}
