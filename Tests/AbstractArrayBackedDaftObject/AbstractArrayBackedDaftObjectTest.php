<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\AbstractArrayBackedDaftObject;

use PHPUnit\Framework\TestCase as Base;
use SignpostMarv\DaftObject\PropertyNotNullableException;

class AbstractArrayBackedDaftObjectTest extends Base
{
    public function test_RetrievePropertyValueFromData__not_nullable() : void
    {
        $obj = new Fixtures\AbstractArrayBackedDaftObject();

        static::expectException(PropertyNotNullableException::class);
        static::expectExceptionMessage(
            'Property not nullable: ' .
            Fixtures\AbstractArrayBackedDaftObject::class .
            '::$foo'
        );

        $obj->public_RetrievePropertyValueFromData('foo');
    }
}
