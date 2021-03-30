<?php
require_once __DIR__ . '/../src/core.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Core as p;


class CoreTest extends TestCase {

    public function testLocate() {
        $is_two = fn($x) => $x === 2;
        $list = [ 5, 2, 3 ];
        $expected = [ 1, 2 ];

        $result = p\locate($list, $is_two);
        $this->assertSame($expected, $result);

        $lambda = p\llocate($is_two);
        $result = $lambda($list);
        $this->assertSame($expected, $result);
    }

    public function testElementWithKeyValueExistsInList() {
        $key = 'target-key';
        $target_value = 'target-value';
        $list = [ ['target-key' => 'not-target'], ['target-key' => 'target-value'] ];

        $result
            = p\element_with_key_value_exists_in_list(
                $list,
                $key,
                $target_value);
        $this->assertTrue($result);

        $lambda
            = p\lelement_with_key_value_exists_in_list(
                $key,
                $target_value);
        $result = $lambda($list);
        $this->assertTrue($result);
    }

}
