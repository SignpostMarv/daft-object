<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\TypeUtilities;
use SignpostMarv\DaftObject\Tests\TestCase;

class TypeUtilitiesTest extends TestCase
{
    /**
    * @psalm-param class-string<AbstractDaftObject> $className
    *
    * @dataProvider dataProvider_AbstractDaftObject__has_properties
    */
    public function test_DaftObjectPublicOrProtectedGetters(string $className) : void
    {
        $expected = count($className::PROPERTIES);

        if (is_a($className, DefinesOwnIdPropertiesInterface::class, true)) {
            $expected += 1;
        }

        static::assertLessThanOrEqual(
            $expected,
            count(TypeUtilities::DaftObjectPublicOrProtectedGetters($className))
        );
    }
}
