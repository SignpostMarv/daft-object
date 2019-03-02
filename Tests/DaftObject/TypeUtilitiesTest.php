<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeUtilities;

class TypeUtilitiesTest extends TestCase
{
    /**
    * @dataProvider dataProvider_AbstractDaftObject__has_properties_but_not_this_one
    *
    * @psalm-param class-string<\SignpostMarv\DaftObject\DaftObject> $class_name
    */
    public function test_hasMethod_is_false(string $class_name, string $property) : void
    {
        static::assertFalse(TypeUtilities::HasMethod($class_name, $property, true, true));
        static::assertFalse(TypeUtilities::HasMethod($class_name, $property, true, false));
        static::assertFalse(TypeUtilities::HasMethod($class_name, $property, false, true));
        static::assertFalse(TypeUtilities::HasMethod($class_name, $property, false, false));
    }
}
