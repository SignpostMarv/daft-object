<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftJson;

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
        static::expectExceptionMessage('Property not json-decodable (should be an array): ::$');

        JsonTypeUtilities::DaftObjectFromJsonTypeArray('', '', [null], true);
    }

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
        ];
    }

    /**
    * @param mixed[] $array
    *
    * @psalm-param class-string<\SignpostMarv\DaftObject\DaftJson> $className
    * @psalm-param class-string<\Throwable> $expection
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
}
