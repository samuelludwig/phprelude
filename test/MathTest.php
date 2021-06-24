<?php declare(strict_types=1); namespace Phprelude\Test\Math;
require_once __DIR__ . '/../src/math.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Math;

class MathTest extends TestCase {

    public function testSum()
    {
        $this->assertSame(2, Math\sum(1, 1));
    }
}
