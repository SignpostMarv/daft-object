<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\TypeUtilities;
use SignpostMarv\DaftObject\Tests\TestCase;

class TypeUtilitiesTest extends TestCase
{
    /**
    * @psalm-param class-string<AbstractDaftObject> $className
    *
    * @dataProvider dataProvider_AbstractDaftObject__is_subclass_of
    */
    public function test_DaftObjectPublicOrProtectedGetters(string $className) : void
    {
        static::assertLessThanOrEqual(
            count($className::PROPERTIES),
            count(TypeUtilities::DaftObjectPublicOrProtectedGetters($className))
        );
    }
}
