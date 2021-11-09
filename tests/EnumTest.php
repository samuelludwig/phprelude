<?php
require_once __DIR__ . '/../src/enum.php';
require_once __DIR__ . '/../src/math.php';
require_once __DIR__ . '/../src/core.php';
require_once __DIR__ . '/../src/integer.php';

use \PHPUnit\Framework\TestCase;
use \Phprelude\Enum;
use \Phprelude\Integer;
use \Phprelude\Math;
use \Phprelude\Core;

class EnumTest extends TestCase {

    public function testLocate() {
        $is_two = fn($x) => $x === 2;
        $list = [ 5, 2, 3 ];
        $expected = [ 1, 2 ];

        $result = Enum\locate($list, $is_two);
        $this->assertSame($expected, $result);
    }

    public function testElementWithKeyValueExists() {
        $key = 'target-key';
        $target_value = 'target-value';
        $list
            = [ ['target-key' => 'not-target']
              , ['target-key' => 'target-value']
              ];

        $result
            = Enum\element_with_key_value_exists(
                $list, $key, $target_value);

        $this->assertTrue($result);
    }

    public function testArrayContainsKeyVal() {
        $array = [ 'name' => 'me', 'pet' => 'cat' ];

        $result1 = Enum\contains_key_vals($array, [ 'pet' => 'cat' ]);
        $this->assertTrue($result1);

        $result2 = Enum\contains_key_vals($array, [ 'orange' => 'dog' ]);
        $this->assertFalse($result2);
    }

    public function testIsTrueForAllElements() {
        $array = [ 1, 2, 3 ];

        $result = Enum\is_true_for_all_elements($array, fn($x) => $x > 0);
        $this->assertTrue($result);

        $result = Enum\is_true_for_all_elements($array, fn($x) => $x > 4);
        $this->assertFalse($result);
    }

    public function testExtractValues() {
        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];
        $result = Enum\extract_values($array, ['my_name', 'my_age']);
        $expected = ['me', 65];
        $this->assertEquals($expected, $result);

        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];
        $result
            = Enum\extract_values_into_format(
                $array,
                ['name' => 'my_name', 'age' => 'my_age']);
        $expected = ['name' => 'me', 'age' => 65];
        $this->assertEquals($expected, $result);
    }

    public function testExtractKeysWhere() {
        $array = ['dog' => 0, 'cat' => 65, 'orange' => 5];
        $result = Enum\extract_keys_where($array, fn($x) => $x > 6);
        $expected = ['cat'];
        $this->assertEquals($expected, $result);
    }

    public function testExtractWhereKeyValue() {
        $array
            = [ ['x' => 1, 'y' => 1]
              , ['x' => 2, 'y' => 2]
              , ['x' => 3, 'y' => 3]
              ];

        $result
            = Enum\extract_where_key_value_matches(
                $array, 'x', fn($x) => $x >= 2);

        $expected = ['x' => 2, 'y' => 2];
        $this->assertEquals($expected, $result);

        $result = Enum\extract_where_key_value_equals($array, 'x', 2);
        $expected = ['x' => 2, 'y' => 2];
        $this->assertEquals($expected, $result);
    }

    public function testExtractKeyVal() {
        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];

        $result = Enum\extract_key_val($array, 'my_age');
        $expected = 65;
        $this->assertEquals($expected, $result);

        $array
            = [ 'me' => ['my_name' => 'me', 'my_age' => 65]
              , 'dogs_name' => 'cat'
              ];
        $result = Enum\extract_nested_key_val($array, ['me', 'my_age']);
        $expected = 65;
        $this->assertEquals($expected, $result);
    }

    public function testUpdateKeyVal() {
        $array = ['my_name' => 'me', 'my_age' => 65, 'dogs_name' => 'cat'];

        $result = Enum\update_key_val($array, 'my_age', fn($x) => $x + 1);
        $expected = ['my_name' => 'me', 'my_age' => 66, 'dogs_name' => 'cat'];
        $this->assertEquals($expected, $result);

        $array
            = [ 'me' => ['my_name' => 'me', 'my_age' => 65]
              , 'dogs_name' => 'cat'
              ];
        $result = Enum\update_nested_key_val($array, ['me', 'my_age'], fn($x) => $x + 1);
        $expected
            = [ 'me' => ['my_name' => 'me', 'my_age' => 66]
              , 'dogs_name' => 'cat'
              ];
        $this->assertEquals($expected, $result);
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

    public function testMap()
    {
        $double = function (int $i): int {
            return $i * 2;
        };

        $iterator = function (): Iterator {
            $i = 1;

            while ($i <= 3) {
                yield $i++;
            }
        };

        $keys = function (int $_, int $key): int {
            return $key;
        };

        $this->assertSame([2, 4, 6], Enum\map([1, 2, 3], $double));
        $this->assertSame([2, 4, 6], Enum\map($iterator(), $double));
        $this->assertSame([0, 1, 2], Enum\map(range(1, 3), $keys));
    }

    public function testLmap()
    {
        $double = Enum\lmap(function (int $i): int {
            return $i * 2;
        });

        $this->assertSame([2, 4, 6], $double([1, 2, 3]));
    }

    public function testFilter()
    {
        $input = ['foo', 'bar', 'baz'];

        $this->assertSame(['foo'], Enum\filter($input, function (string $value): bool {
            return $value === 'foo';
        }));
    }

    public function testLazyFilter()
    {
        $input = [1, 2, 3, 4];

        $even = function (int $n): bool {
            return ($n % 2) === 0;
        };

        $this->assertSame([2, 4], Enum\lfilter($even)($input));
    }

    public function testFind()
    {
        $list = [1, 2, 3];
        $is_even = fn($x) => Integer\even($x);
        $this->assertSame(2, Enum\find($list, $is_even));

        $fst_even = Enum\lfind($is_even);
        $this->assertSame(2, $fst_even($list));

        $this->assertSame(0, Enum\find($list, Core\equal(0), 0));
    }

    public function testSort()
    {
        $list = [1, 2, 3];

        $desc = function (int $a, int $b): int {
            return $b <=> $a;
        };

        $this->assertSame([3, 2, 1], Enum\sort($list, $desc));

        $sort_desc = Enum\lsort($desc);
        $this->assertSame([3, 2, 1], $sort_desc($list));

        $this->assertSame($list, $list);
    }

    public function testFirst()
    {
        $desc = function (int $a, int $b): int {
            return $b <=> $a;
        };

        $list = [];
        $this->assertNull(Enum\first($list, $desc));
        $this->assertSame(42, Enum\first($list, $desc, 42));

        $list = [1, 2, 3];
        $this->assertSame(3, Enum\first($list, $desc));

        $higher = Enum\lfirst($desc);
        $this->assertSame(3, $higher($list));
    }

    public function testFold()
    {
        $this->assertSame(6, Enum\fold([1, 2, 3], 0, fn($x, $y) => Math\sum($x, $y)));
    }

    public function testLazyJoin()
    {
        $pieces = ['foo', 'bar', 'baz'];
        $this->assertSame('foobarbaz', Enum\ljoin()($pieces));
        $this->assertSame('foo,bar,baz', Enum\ljoin(',')($pieces));
    }

    public function testHasKeys() {
        $a = ['dog' => 'yes', 'name' => 'kot'];
        $this->assertTrue(Enum\has_keys($a, ['name']));
        $this->assertTrue(Enum\has_keys($a, ['dog', 'name']));
        $this->assertFalse(Enum\has_keys($a, ['dog', 'name', 'breed']));
    }

    public function testMergePreserveKeys() {
        $a1 = [2 => 'dog', 3 => 'cat'];
        $a2 = [3 => 'orange', 'x' => 'door'];
        $a3 = ['x' => 'yellow'];
        $expected = [2 => 'dog', 3 => 'orange', 'x' => 'yellow'];
        $this->assertEquals(Enum\merge_preserve_keys($a1, $a2, $a3), $expected);
    }

    public function testSumKeyValues() {
        $a = ['one' => 10, 'two' => 20];
        $b = ['one' => 30, 'two' => 60];
        $both = [$a, $b];

        $expected = ['one' => 40, 'two' => 80];
        $this->assertEquals($expected, Enum\sum_key_values($a, $b));
        $this->assertEquals($expected, Enum\sum_key_values(...$both));

        $this->assertEquals($expected, Enum\lsum_key_values()($a, $b));
        $this->assertEquals($expected, Enum\lsum_key_values()(...$both));
    }

    public function testIsSubsetAndSuperset() {
        $a = [10, 20, 30];
        $b = [10, 20, 30, 40];
        $c = ['one' => 10, 'two' => 20, 'three' => 30];
        $d = ['one' => 10, 'two' => 20, 'three' => 30, 'four' => 40];

        $this->assertTrue(Enum\is_subset_of($a, $b));
        $this->assertFalse(Enum\is_superset_of($a, $b));

        $this->assertTrue(Enum\is_superset_of($b, $a));
        $this->assertFalse(Enum\is_subset_of($b, $a));

        $this->assertTrue(Enum\is_subset_of($c, $d));
        $this->assertFalse(Enum\is_superset_of($c, $d));

        $this->assertTrue(Enum\is_superset_of($d, $c));
        $this->assertFalse(Enum\is_subset_of($d, $c));

        $is_subset_of_b = Enum\lis_subset_of($b);
        $this->assertTrue($is_subset_of_b($a));
    }

    public function testGroupBy() {
        $a = ['x' => 'one', 'y' => 'one', 'z' => 'one'];
        $b = ['x' => 'two', 'y' => 'two', 'z' => 'two'];
        $c = ['x' => 'one', 'y' => 'three', 'z' => 'three'];
        $d = ['x' => 'one', 'y' => 'one', 'z' => 'four'];
        $arrays = [$a, $b, $c, $d];

        $grouped_by_x = ['one' => [$a, $c, $d], 'two' => [$b]];
        //$grouped_by_x_and_y = [[$a, $c, $d], $b];

        $this->assertEquals($grouped_by_x, Enum\group_by($arrays, 'x'));
    }

    public function test_zip_as_field()
    {
        $f = fn($h, $d, $f) => Core\zip_as_field($h, $d, $f);

        $host = [];
        $data = [];
        $field = "name";
        $result = $f($host, $data, $field);
        $expected = [];
        $this->assertEquals($expected, $result);

        $host = ['a'];
        $data = ['b'];
        $field = "name";
        $result = $f($host, $data, $field);
        $expected = [ [0 => 'a', 'name' => 'b'] ];
        $this->assertEquals($expected, $result);

        $host = [ ['first' => 'john'], ['first' => 'bill'] ];
        $data = [ 'smith', 'robertson', 'andrews' ];
        $field = "last";
        $result = $f($host, $data, $field);
        $expected = [ 
            ['first' => 'john', 'last' => 'smith'], 
            ['first' => 'bill', 'last' => 'robertson'],
        ];
        $this->assertEquals($expected, $result);

        $host = [];
        $data = [ 'smith', 'robertson', 'andrews' ];
        $field = "last";
        $result = $f($host, $data, $field);
        $expected = [];
        $this->assertEquals($expected, $result);
    }
}
