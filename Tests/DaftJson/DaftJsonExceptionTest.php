<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftJson;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\PropertyNotJsonDecodableException;
use SignpostMarv\DaftObject\PropertyNotJsonDecodableShouldBeArrayException;
use SignpostMarv\DaftObject\PropertyNotNullableException;
use SignpostMarv\DaftObject\ReadWriteJson;
use SignpostMarv\DaftObject\ReadWriteJsonJson;
use SignpostMarv\DaftObject\ReadWriteJsonJsonArray;
use SignpostMarv\DaftObject\ReadWriteJsonJsonArrayBad;
use SignpostMarv\DaftObject\Tests\TestCase;

class DaftJsonExceptionTest extends TestCase
{
    public function dataProviderClassDoesNotImplementClassException() : array
    {
        return [
            [
                ReadWriteJsonJsonArrayBad::class,
                'stdClass',
                [
                    'json' => [
                        [
                            'Foo' => 'Foo',
                            'Bar' => 1.0,
                            'Baz' => 2,
                            'Bat' => true,
                        ],
                    ],
                ],
                true,
            ],
        ];
    }

    public function dataProviderPropertyNotThingableException() : array
    {
        return [
            [
                ReadWriteJsonJsonArrayBad::class,
                ReadWriteJsonJsonArrayBad::class,
                'json',
                PropertyNotNullableException::class,
                'nullable',
                [
                    'json' => null,
                ],
                true,
            ],
        ];
    }

    /**
    * @dataProvider dataProviderClassDoesNotImplementClassException
    *
    * @psalm-param class-string<AbstractArrayBackedDaftObject> $implementation
    */
    public function testClassDoesNotImplementClassException(
        string $implementation,
        string $expectingFailureWith,
        array $args,
        bool $writeAll
    ) : void {
        static::assertTrue(class_exists($implementation));

        $this->expectException(ClassDoesNotImplementClassException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $expectingFailureWith,
            DaftJson::class
        ));

        $implementation::DaftObjectFromJsonArray($args, $writeAll);
    }

    /**
    * @dataProvider dataProviderPropertyNotThingableException
    *
    * @psalm-param class-string<DaftJson> $implementation
    */
    public function testPropertyNotThingableException(
        string $implementation,
        string $expectingFailureWithClass,
        string $expectingFailureWithProperty,
        string $expectingException,
        string $expectingThing,
        array $args,
        bool $writeAll
    ) : void {
        static::assertTrue(class_exists($implementation));

        $this->expectException($expectingException);
        $this->expectExceptionMessage(sprintf(
            'Property not %s: %s::$%s',
            $expectingThing,
            $expectingFailureWithClass,
            $expectingFailureWithProperty
        ));

        $obj = $implementation::DaftObjectFromJsonArray($args, $writeAll);

        /**
        * @var array<int, string>
        */
        $args = $implementation::DaftObjectPublicGetters();

        foreach ($args as $arg) {
            $obj->__get($arg);
        }
    }
}
