<?php declare(strict_types=1); namespace Phprelude\Test\Core;
require_once __DIR__ . '/../src/optics.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Optics as o;

class OpticsTest extends TestCase {

    public function testIso() {
        $result = o\iso();
        $expected = [];
        $this->assertEquals($expected, $result);
    }
}

