<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinesOwnIdPropertiesInterface;

use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\Tests\TestCase as Base;

class DefinesOwnIdPropertiesInterfaceTest extends Base
{
    /**
    * @return array<int, array<int, scalar>>
    *
    * @psalm-return array<int, array{0:scalar}>
    */
    public function dataProvider_DefinesOwnScalarIdProperties() : array
    {
        return [
            [1.2],
            ['three'],
            [true],
            [false],
            [3],
        ];
    }

    /**
    * @dataProvider dataProvider_DefinesOwnScalarIdProperties
    *
    * @param scalar $id
    */
    public function test_DefinesOwnScalarIdProperties($id) : void
    {
        $obj = new Fixtures\DefinesOwnScalarIdProperties(['id' => $id]);

        static::assertSame($id, $obj->id);

        if (is_float($id)) {
            $obj = new Fixtures\DefinesOwnFloatIdProperties(['id' => $id]);

            static::assertSame($id, $obj->id);
        } elseif (is_int($id)) {
            $obj = new Fixtures\DefinesOwnIntIdProperties(['id' => $id]);

            static::assertSame($id, $obj->id);
        }

        $obj = new Fixtures\DefinesOwnArrayIdProperties([
            'id' => [
                'foo' => $id,
            ],
        ]);

        static::assertSame(
            [
                'foo' => $id,
            ],
            $obj->id
        );
    }

    /**
    * @dataProvider dataProvider_DefinesOwnIdPropertiesInterface_NonAbstract
    *
    * @psalm-param class-string<DefinesOwnIdPropertiesInterface> $className
    */
    public function test_DefinesIdPropertiesCorrectly(string $className) : void
    {
        $id_args = $className::DaftObjectIdProperties();

        $props = $className::DaftObjectProperties();

        static::assertGreaterThan(0, count($props));
        static::assertGreaterThan(0, count($id_args));

        foreach ($id_args as $arg) {
            static::assertContains($arg, $props);
        }
    }
}
