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

    public function testArrayContainsKeyVal() {
        $array = [ 'name' => 'me', 'pet' => 'cat' ];

        $result1
            = p\array_contains_key_vals(
                $array,
                [ 'pet' => 'cat' ]);

        $this->assertTrue($result1);

        $result2
            = p\array_contains_key_vals(
                $array,
                [ 'orange' => 'dog' ]);

        $this->assertFalse($result2);

        $lambda
            = p\larray_contains_key_vals(['pet' => 'cat']);
        $lambda_result = $lambda($array);
        $this->assertTrue($lambda_result);
    }

    public function testIsTrueForAllElements() {
        $array = [ 1, 2, 3 ];

        $result
            = p\is_true_for_all_elements(
                $array,
                fn($x) => $x > 0);

        $this->assertTrue($result);

        $lambda
            = p\lis_true_for_all_elements(fn($x) => $x > 0);
        $lambda_result = $lambda($array);
        $this->assertTrue($lambda_result);

        $result
            = p\is_true_for_all_elements(
                $array,
                fn($x) => $x > 4);

        $this->assertFalse($result);

        $lambda
            = p\lis_true_for_all_elements(fn($x) => $x > 4);
        $lambda_result = $lambda($array);
        $this->assertFalse($lambda_result);
    }

    public function testExtractValuesFromArray() {
        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];
        $result = p\extract_values_from_array($array, ['my_name', 'my_age']);
        $expected = ['me', 65];
        $this->assertEquals($expected, $result);

        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];
        $result
            = p\extract_values_from_array_into_format(
                $array,
                ['name' => 'my_name', 'age' => 'my_age']);
        $expected = ['name' => 'me', 'age' => 65];
        $this->assertEquals($expected, $result);

        $lambda
            = p\lextract_values_from_array_into_format(
                ['name' => 'my_name', 'age' => 'my_age']);

        $lambda_result = $lambda($array);
        $this->assertEquals($expected, $lambda_result);
    }

    public function testFilterUniqueArrays() {
        $arrays
            = [ ['a' => 'b', 'c' => 'd', 'e' => ['f', 'g']]
              , ['a' => 'b', 'c' => 'd', 'e' => ['f', 'g']]
              , ['a' => 'b', 'c' => 'd', 'e' => ['x', 'y']]
              , ['a' => 'b', 'c' => 'd', 'z' => ['x', 'y']]
              ];

        $result
            = p\filter_unique_arrays($arrays);

        $expected
            = [ ['a' => 'b', 'c' => 'd', 'e' => ['f', 'g']]
              , ['a' => 'b', 'c' => 'd', 'e' => ['x', 'y']]
              , ['a' => 'b', 'c' => 'd', 'z' => ['x', 'y']]
              ];

        $this->assertEqualsCanonicalizing($expected, $result);

        $lambda = p\lfilter_unique_arrays();

        $lambda_result = $lambda($arrays);

        $this->assertEqualsCanonicalizing($expected, $lambda_result);
    }

    public function testEach() {
        $modifier = fn($_) => [];
        $array = [ 'a', 'b', 'c' ];

        $result = p\each($array, $modifier);
        $expected = [ [], [], [] ];

        $this->assertEquals($expected, $result);

        $lambda = p\leach($modifier);
        $lambda_result = $lambda($array);

        $this->assertEquals($expected, $lambda_result);

        $modifier = fn($k, $v) => "$k$v";
        $array = [ 'a' => 1, 'b' => 2, 'c' => 3 ];

        $result = p\each_with_index($array, $modifier);
        $expected = [ 'a1', 'b2', 'c3' ];

        $this->assertEquals($expected, $result);

        $modifier = fn($k, $v) => "$k$v";
        $array = [ 'a' => 1, 'b' => 2, 'c' => 3 ];

        $result = p\map_with_index($array, $modifier);
        $expected = [ 'a' => 'a1', 'b' => 'b2', 'c' => 'c3' ];

        $this->assertEquals($expected, $result);
    }
}
