<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftSortableObject;

use Generator;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\DaftSortableObject;
use SignpostMarv\DaftObject\TraitSortableDaftObject;
use SignpostMarv\DaftObject\Tests\TestCase as Base;

/**
* @template T as DaftSortableObject
*
* @template-extends Base<T>
*/
class DaftSortableObjectTest extends Base
{
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
            if(
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
        static::assertIsArray($className::SORTABLE_PROPERTIES);
        static::assertGreaterThan(
            0,
            count($className::SORTABLE_PROPERTIES),
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
            count($className::SORTABLE_PROPERTIES),
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

        foreach ($className::SORTABLE_PROPERTIES as $k => $v) {
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

        static::assertSame(count($className::SORTABLE_PROPERTIES), array_sum($prop_counts));
    }

    /**
    * @depends test_SortableDaftObject_and_AbstractDaftObject
    */
    public function test_DaftSortableObject_CompareToDaftSortableObject() : void
    {
        $a = new Fixtures\DaftSortableObject(['intSortOrder' => 1]);
        $b = new Fixtures\DaftSortableObject(['intSortOrder' => 1]);

        static::assertSame(1, $a->intSortOrder);
        static::assertSame(1, $b->intSortOrder);

        static::assertSame(0, 1 <=> 1);
        static::assertSame(0, $a->CompareToDaftSortableObject($a));
        static::assertSame(0, $b->CompareToDaftSortableObject($b));
        static::assertSame(0, $a->CompareToDaftSortableObject($b));

        $b->intSortOrder = 2;
        static::assertSAme(2, $b->intSortOrder);
        static::assertSame(-1, 1 <=> 2);
        static::assertSame(-1, $a->CompareToDaftSortableObject($b));

        $b->intSortOrder = -10;
        static::assertSAme(-10, $b->intSortOrder);
        static::assertSame(1, 1 <=> -10);
        static::assertSame(1, $a->CompareToDaftSortableObject($b));
    }
}
