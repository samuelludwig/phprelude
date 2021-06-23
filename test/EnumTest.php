<?php
require_once __DIR__ . '/../src/enum.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Enum;

class CoreTest extends TestCase {

    public function testLocate() {
        $is_two = fn($x) => $x === 2;
        $list = [ 5, 2, 3 ];
        $expected = [ 1, 2 ];

        $result = Enum\locate($list, $is_two);
        $this->assertSame($expected, $result);
    }

    public function testElementWithKeyValueExistsInList() {
        $key = 'target-key';
        $target_value = 'target-value';
        $list = [ ['target-key' => 'not-target'], ['target-key' => 'target-value'] ];

        $result
            = Enum\element_with_key_value_exists_in_list(
                $list,
                $key,
                $target_value);
        $this->assertTrue($result);
    }

    public function testArrayContainsKeyVal() {
        $array = [ 'name' => 'me', 'pet' => 'cat' ];

        $result1
            = Enum\array_contains_key_vals(
                $array,
                [ 'pet' => 'cat' ]);

        $this->assertTrue($result1);

        $result2
            = Enum\array_contains_key_vals(
                $array,
                [ 'orange' => 'dog' ]);

        $this->assertFalse($result2);
    }

    public function testIsTrueForAllElements() {
        $array = [ 1, 2, 3 ];

        $result
            = Enum\is_true_for_all_elements(
                $array,
                fn($x) => $x > 0);

        $this->assertTrue($result);

        $result
            = Enum\is_true_for_all_elements(
                $array,
                fn($x) => $x > 4);

        $this->assertFalse($result);
    }

    public function testExtractValuesFromArray() {
        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];
        $result = Enum\extract_values_from_array($array, ['my_name', 'my_age']);
        $expected = ['me', 65];
        $this->assertEquals($expected, $result);

        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];
        $result
            = Enum\extract_values_from_array_into_format(
                $array,
                ['name' => 'my_name', 'age' => 'my_age']);
        $expected = ['name' => 'me', 'age' => 65];
        $this->assertEquals($expected, $result);

        $lambda
            = Enum\lextract_values_from_array_into_format(
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
            = Enum\filter_unique_arrays($arrays);

        $expected
            = [ ['a' => 'b', 'c' => 'd', 'e' => ['f', 'g']]
              , ['a' => 'b', 'c' => 'd', 'e' => ['x', 'y']]
              , ['a' => 'b', 'c' => 'd', 'z' => ['x', 'y']]
              ];

        $this->assertEqualsCanonicalizing($expected, $result);

        $lambda = Enum\lfilter_unique_arrays();

        $lambda_result = $lambda($arrays);

        $this->assertEqualsCanonicalizing($expected, $lambda_result);
    }

    public function testEach() {
        $modifier = fn($_) => [];
        $array = [ 'a', 'b', 'c' ];

        $result = Enum\each($array, $modifier);
        $expected = [ [], [], [] ];

        $this->assertEquals($expected, $result);

        $lambda = Enum\leach($modifier);
        $lambda_result = $lambda($array);

        $this->assertEquals($expected, $lambda_result);

        $modifier = fn($k, $v) => "$k$v";
        $array = [ 'a' => 1, 'b' => 2, 'c' => 3 ];

        $result = Enum\each_with_index($array, $modifier);
        $expected = [ 'a1', 'b2', 'c3' ];

        $this->assertEquals($expected, $result);

        $modifier = fn($k, $v) => "$k$v";
        $array = [ 'a' => 1, 'b' => 2, 'c' => 3 ];

        $result = Enum\map_with_index($array, $modifier);
        $expected = [ 'a' => 'a1', 'b' => 'b2', 'c' => 'c3' ];

        $this->assertEquals($expected, $result);
    }
}
