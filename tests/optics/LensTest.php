<?php declare(strict_types=1); namespace Phprelude\Test\Core;
require_once __DIR__ . '/../../src/optics/lens.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Optics\Lens as l;

class LensTest extends TestCase {

    public function testView() {
        $source = ['name' => 'mark', 'species' => 'cat'];
        $species = l\mk_lens('species');
        $result = l\view($species)($source);
        $expected = 'cat';
        $this->assertEquals($expected, $result);
    }

    public function testSet() {
        $source = ['name' => 'mark', 'species' => 'cat'];
        $species = l\mk_lens('species');
        $result = l\set($species, 'dog')($source);
        $expected = ['name' => 'mark', 'species' => 'dog'];
        $this->assertEquals($expected, $result);
    }

    public function testOver() {
        $source = ['name' => 'mark', 'species' => 'cat'];
        $species = l\mk_lens('species');
        $result = l\over($species, fn($x) => strtoupper($x))($source);
        $expected = ['name' => 'mark', 'species' => 'CAT'];
        $this->assertEquals($expected, $result);
    }

    public function testComposeView() {
        $source = ['name' => 'mark', 'body' => ['height' => 100, 'weight' => 86]];
        $body = l\mk_lens('body');
        $height = l\mk_lens('height');
        $body_height = l\compose($body, $height);
        $result = l\view($body_height)($source);
        $expected = 100;
        $this->assertEquals($expected, $result);
    }

    public function testComposeSet() {
        $source = ['name' => 'mark', 'body' => ['height' => 100, 'weight' => 86]];
        $body = l\mk_lens('body');
        $height = l\mk_lens('height');
        $body_height = l\compose($body, $height);
        $new_source = l\set($body_height, 110)($source);
        $result = l\view($body_height)($new_source);
        $expected = 110;
        $this->assertEquals($expected, $result);

        $source =
            [ 'name' => 'mark'
            , 'body' => ['height' => ['val' => 100, 'unit' => 'cm'], 'weight' => 86]
            ];
        $body = l\mk_lens('body');
        $height = l\mk_lens('height');
        $height_val = l\mk_lens('val');
        $height = l\compose($body, $height, $height_val);
        $new_source = l\set($height, 110)($source);
        $result = l\view($height)($new_source);
        $expected = 110;
        $this->assertEquals($expected, $result);
    }

    public function testComposeModify() {
        $source = ['name' => 'mark', 'body' => ['height' => 100, 'weight' => 86]];
        $body = l\mk_lens('body');
        $height = l\mk_lens('height');
        $body_height = l\compose($body, $height);
        $new_source = l\over($body_height, fn($x) => $x + 10)($source);
        $result = l\view($body_height)($new_source);
        $expected = 110;
        $this->assertEquals($expected, $result);
    }

    public function testComposeWithNonTrivialAccessors() {
        $source =
            [ 'name' => 'mark'
            , 'body' => ['height' => ['val' => 100, 'unit' => 'cm'], 'weight' => 86]
            ];
        $body = l\mk_lens('body');
        $height = l\mk_lens('height');

        $unit_get = fn($source) => strtoupper($source['unit']);
        $unit_set = function ($source, $val) {
            $source['unit'] = strtolower($val);
            return $source;
        };
        $unit = l\lens($unit_get, $unit_set);

        $h_unit = l\compose($body, $height, $unit);

        $get_unit = l\view($h_unit);
        $result = $get_unit($source);
        $expected = 'CM';
        $this->assertEquals($expected, $result);

        $result = l\set($h_unit, 'IN')($source)['body']['height']['unit'];
        $expected = 'in';
        $this->assertEquals($expected, $result);
    }

    public function testMkLensesFor() {
        $_t = [ 'k1' => [['string']], 'k2' => [['int']] ];
        $source = ['k1' => 'dog', 'k2' => 5];
        $t = l\mk_lenses_for($_t);
        $result = l\view($t['k2'])($source);
        $expected = 5;
        $this->assertEquals($expected, $result);
    }

}

