<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\AbstractDaftObject;

use Generator;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\NotPublicGetterPropertyException;
use SignpostMarv\DaftObject\NotPublicSetterPropertyException;
use SignpostMarv\DaftObject\Tests\DaftObject\DaftObjectImplementationTest;
use SignpostMarv\DaftObject\TypeUtilities;
use SignpostMarv\DaftObject\UndefinedPropertyException;

/**
* @template T as AbstractDaftObject
*
* @template-extends DaftObjectImplementationTest<T>
*/
class AbstractDaftObjectImplementationTest extends DaftObjectImplementationTest
{
    public function dataProvider_FuzzingImplementations_NotPublicGetter() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzing() as $args) {
            $className = $args[0];

            $properties = $className::DaftObjectProperties();

            $getters = TypeUtilities::DaftObjectPublicGetters($className);

            foreach ($properties as $property) {
                if ( ! in_array($property, $getters, true)) {
                    yield [$className, $property];
                }
            }
        }
    }

    public function dataProvider_FuzzingImplementations_NotPublicSetter() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzing() as $args) {
            $className = $args[0];

            $properties = $className::DaftObjectProperties();

            $setters = TypeUtilities::DaftObjectPublicSetters($className);

            foreach ($properties as $property) {
                if ( ! in_array($property, $setters, true)) {
                    yield [$className, $property];
                }
            }
        }
    }

    public function dataProvider_FuzzingImplementations_NotDefined() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzing() as $args) {
            $className = $args[0];

            $properties = $className::DaftObjectProperties();

            $seitreporp = array_map('strrev', $properties);

            foreach ($seitreporp as $property) {
                if ( ! in_array($property, $properties, true)) {
                    yield [$className, $property];
                }
            }
        }
    }

    /**
    * @dataProvider dataProvider_FuzzingImplementations_NotPublicGetter
    *
    * @psalm-param class-string<T> $className
    */
    public function test_FuzzingImplementations_NotPublicGetter(
        string $className,
        string $property
    ) : void {
        $obj = new $className();

        static::expectException(NotPublicGetterPropertyException::class);
        static::expectExceptionMessage(
            'Property not a public getter: ' .
            $className .
            '::$' .
            $property
        );

        $obj->__get($property);
    }

    /**
    * @dataProvider dataProvider_FuzzingImplementations_NotPublicSetter
    *
    * @psalm-param class-string<T> $className
    */
    public function test_FuzzingImplementations_NotPublicSetter(
        string $className,
        string $property
    ) : void {
        $obj = new $className();

        static::expectException(NotPublicSetterPropertyException::class);
        static::expectExceptionMessage(
            'Property not a public setter: ' .
            $className .
            '::$' .
            $property
        );

        $obj->__set($property, $property);
    }

    /**
    * @dataProvider dataProvider_FuzzingImplementations_NotDefined
    *
    * @psalm-param class-string<T> $className
    */
    public function test_FuzzingImplementations_NotDefined(
        string $className,
        string $property
    ) : void {
        $obj = new $className();

        static::expectException(UndefinedPropertyException::class);
        static::expectExceptionMessage(
            'Property not defined: ' .
            $className .
            '::$' .
            $property
        );

        $obj->__get($property);
    }
}
