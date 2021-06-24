<?php declare(strict_types=1); namespace Phprelude\Test\Integer;
require_once __DIR__ . '/../src/integer.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Integer;

class IntegerTest extends TestCase {
    public function testEvenOdd() {
        $this->assertTrue(Integer\even(2));
        $this->assertFalse(Integer\odd(2));
        $this->assertFalse(Integer\even(1));
        $this->assertTrue(Integer\odd(1));
    }
}
