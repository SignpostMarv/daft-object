<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinesOwnIdPropertiesInterface;

use PHPUnit\Framework\TestCase as Base;

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
}
