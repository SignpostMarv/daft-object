<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftJson;

use Generator;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\DaftObjectNotDaftJsonBadMethodCallException;
use SignpostMarv\DaftObject\JsonTypeUtilities;
use SignpostMarv\DaftObject\LinkedData\HasArrayOfHasId;
use SignpostMarv\DaftObject\LinkedData\HasId;
use SignpostMarv\DaftObject\PropertyNotJsonDecodableException;
use SignpostMarv\DaftObject\PropertyNotJsonDecodableShouldBeArrayException;
use SignpostMarv\DaftObject\Tests\TestCase;

class JsonTypeUtilitiesExceptionsTest extends TestCase
{
    public function test_DaftObjectFromJsonTypeArray_fails_with_non_array() : void
    {
        static::expectException(PropertyNotJsonDecodableShouldBeArrayException::class);
        static::expectExceptionMessage(
            'Property not json-decodable (should be an array): ' .
            DaftJson::class .
            '::$'
        );

        JsonTypeUtilities::DaftObjectFromJsonTypeArray(DaftJson::class, '', [null], true);
    }

    /**
    * @psalm-return array<int, array{0:class-string<DaftJson>, 1:array, 2:class-string<\Throwable>, 2:string}>
    */
    public function dataProvider_ThrowIfJsonDefNotValid_fails() : array
    {
        return [
            [
                HasArrayOfHasId::class,
                [
                    null,
                ],
                PropertyNotJsonDecodableException::class,
                (
                    'Property not json-decodable: ' .
                    HasArrayOfHasId::class .
                    '::$0'
                ),
            ],
            [
                HasArrayOfHasId::class,
                [
                    'json' => 1,
                ],
                PropertyNotJsonDecodableShouldBeArrayException::class,
                (
                    'Property not json-decodable (should be an array): ' .
                    HasArrayOfHasId::class .
                    '::$json'
                ),
            ],
            [
                HasArrayOfHasId::class,
                [
                    'single' => 1,
                ],
                PropertyNotJsonDecodableShouldBeArrayException::class,
                (
                    'Property not json-decodable (should be an array): ' .
                    HasId::class .
                    '::$single'
                ),
            ],
        ];
    }

    /**
    * @param mixed[] $array
    *
    * @psalm-param class-string<\SignpostMarv\DaftObject\DaftJson> $className
    * @psalm-param class-string<\Throwable> $exception
    *
    * @dataProvider dataProvider_ThrowIfJsonDefNotValid_fails
    */
    public function test_ThrowIfJsonDefNotValid_fails(
        string $className,
        array $array,
        string $exception,
        string $message
    ) : void {
        static::expectException($exception);
        static::expectExceptionMessage($message);

        JsonTypeUtilities::ThrowIfJsonDefNotValid($className, $array);
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<AbstractDaftObject>}, mixed, void>
    */
    final public function dataProvider_AbstractDaftObject__is_subclass_of__not_DaftJson() : Generator
    {
        foreach ($this->dataProvider_AbstractDaftObject__is_subclass_of() as $args) {
            if ( ! is_subclass_of($args[0], DaftJson::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-param class-string<AbstractDaftObject> $className
    *
    * @dataProvider dataProvider_AbstractDaftObject__is_subclass_of__not_DaftJson
    */
    public function test_ThrowIFNotDaftJson_fails(string $className) : void
    {
        static::expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
        static::expectExceptionMessage(
            $className .
            ' does not implement ' .
            DaftJson::class
        );

        JsonTypeUtilities::ThrowIfNotDaftJson($className);
    }
}
