<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftSortableObject;

use Generator;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftSortableObject;
use SignpostMarv\DaftObject\Exceptions\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\Tests\TestCase as Base;
use SignpostMarv\DaftObject\TraitSortableDaftObject;

/**
* @template T as DaftSortableObject
*
* @template-extends Base<T>
*/
class DaftSortableObjectTest extends Base
{
    const SORT_ORDER_TEST_VALUE_DEFAULT = 1;

    const SORT_ORDER_TEST_VALUE_LESSTHAN = 2;

    const SORT_ORDER_TEST_VALUE_GREATERTHAN = -10;

    const SORT_ORDER_TEST_COMPARE_EQUAL = 0;

    const SORT_ORDER_TEST_COMPARE_LESSTHAN = -1;

    const SORT_ORDER_TEST_COMPARE_GREATERTHAN = 1;

    /**
    * @psalm-return Generator<int, array{0:class-string<AbstractDaftObject>}, mixed, void>
    */
    public function dataProvider_UsesTrait_NotInterface() : Generator
    {
        foreach ($this->dataProvider_AbstractDaftObject__has_properties() as $args) {
            if (
                in_array(TraitSortableDaftObject::class, class_uses($args[0]), true) &&
                ! is_a($args[0], DaftSortableObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<AbstractDaftObject&DaftSortableObject>}, mixed, void>
    */
    final public function dataProvider_SortableDaftObject_and_AbstractDaftObject__has_sortable_properties() : Generator
    {
        foreach ($this->dataProvider_AbstractDaftObject__has_properties() as $args) {
            if (
                is_a($args[0], DaftSortableObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    /**
    * @dataProvider dataProvider_UsesTrait_NotInterface
    *
    * @psalm-param class-string<AbstractDaftObject> $className
    *
    * @psalm-suppress UndefinedMethod
    */
    public function test_TraitSortableDaftObject_CompareFails(string $className) : void
    {
        static::expectException(ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(
            $className .
            ' does not implement ' .
            DaftSortableObject::class
        );

        $className::DaftSortableObjectProperties();
    }

    /**
    * @dataProvider dataProvider_SortableDaftObject_and_AbstractDaftObject__has_sortable_properties
    *
    * @psalm-param class-string<AbstractDaftObject&DaftSortableObject> $className
    */
    public function test_SortableDaftObject_and_AbstractDaftObject(string $className) : void
    {
        /**
        * @var scalar|array|object|null
        */
        $sortable_properties = $className::SORTABLE_PROPERTIES;

        static::assertIsArray($sortable_properties);

        /**
        * @var (scalar|array|object|null)[]
        */
        $sortable_properties = $sortable_properties;

        static::assertGreaterThan(
            0,
            count($sortable_properties),
            (
                $className .
                ' implements ' .
                DaftSortableObject::class .
                ' and should have properties present on ' .
                $className .
                '::SORTABLE_PROPERTIES'
            )
        );
        static::assertGreaterThanOrEqual(
            count($sortable_properties),
            count($className::DaftSortableObjectProperties()),
            (
                $className .
                ' implements ' .
                DaftSortableObject::class .
                ' and should have the same or equal number of properties present on ' .
                $className .
                '::SORTABLE_PROPERTIES as on ' .
                $className .
                '::DaftSortableObjectProperties()'
            )
        );

        $prop_counts = [];

        foreach ($sortable_properties as $k => $v) {
            static::assertIsInt(
                $k,
                (
                    'Array keys of ' .
                    $className .
                    '::SORTABLE_PROPERTIES must be integers!'
                )
            );
            static::assertIsString(
                $v,
                (
                    'Array values of ' .
                    $className .
                    '::SORTABLE_PROPERTIES must be strings!'
                )
            );

            /**
            * @var string
            */
            $v = $v;

            static::assertSame(
                $v,
                trim($v),
                (
                    'Array values of ' .
                    $className .
                    '::SORTABLE_PROPERTIES must be must not contain trailing whitespace'
                )
            );

            $v_lc = mb_strtolower($v);

            if ( ! isset($prop_counts[$v_lc])) {
                $prop_counts[$v_lc] = 0;
            }

            ++$prop_counts[$v_lc];
        }

        static::assertSame(count($sortable_properties), array_sum($prop_counts));
    }

    /**
    * @depends test_SortableDaftObject_and_AbstractDaftObject
    */
    public function test_DaftSortableObject_CompareToDaftSortableObject() : void
    {
        $a = new Fixtures\DaftSortableObject(['intSortOrder' => 1]);
        $b = new Fixtures\DaftSortableObject(['intSortOrder' => 1]);

        static::assertSame(self::SORT_ORDER_TEST_VALUE_DEFAULT, $a->intSortOrder);
        static::assertSame(self::SORT_ORDER_TEST_VALUE_DEFAULT, $b->intSortOrder);

        static::assertSame(
            self::SORT_ORDER_TEST_COMPARE_EQUAL,
            self::SORT_ORDER_TEST_VALUE_DEFAULT <=> self::SORT_ORDER_TEST_VALUE_DEFAULT
        );
        static::assertSame(
            self::SORT_ORDER_TEST_COMPARE_EQUAL,
            $a->CompareToDaftSortableObject($a)
        );
        static::assertSame(
            self::SORT_ORDER_TEST_COMPARE_EQUAL,
            $b->CompareToDaftSortableObject($b)
        );
        static::assertSame(
            self::SORT_ORDER_TEST_COMPARE_EQUAL,
            $a->CompareToDaftSortableObject($b)
        );

        $b->intSortOrder = self::SORT_ORDER_TEST_VALUE_LESSTHAN;
        static::assertSame(self::SORT_ORDER_TEST_VALUE_LESSTHAN, $b->__get('intSortOrder'));
        static::assertSame(
            -1,
            self::SORT_ORDER_TEST_VALUE_DEFAULT <=> self::SORT_ORDER_TEST_VALUE_LESSTHAN
        );
        static::assertSame(-1, $a->CompareToDaftSortableObject($b));

        $b->intSortOrder = self::SORT_ORDER_TEST_VALUE_GREATERTHAN;
        static::assertSame(self::SORT_ORDER_TEST_VALUE_GREATERTHAN, $b->__get('intSortOrder'));
        static::assertSame(
            self::SORT_ORDER_TEST_VALUE_DEFAULT,
            self::SORT_ORDER_TEST_VALUE_DEFAULT <=> self::SORT_ORDER_TEST_VALUE_GREATERTHAN
        );
        static::assertSame(
            self::SORT_ORDER_TEST_VALUE_DEFAULT,
            $a->CompareToDaftSortableObject($b)
        );
    }
}
