<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\AbstractArrayBackedDaftObject;

use PHPUnit\Framework\TestCase as Base;
use SignpostMarv\DaftObject\Exceptions\PropertyNotNullableException;
use SignpostMarv\DaftObject\Exceptions\PropertyValueNotOfExpectedTypeException;

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

    /**
    * @psalm-return array<int, array{0:scalar, 1:string|null}>
    */
    public function dataProvider_RetrievePropertyValueFromDataExpectStringOrNull() : array
    {
        return [
            [
                1,
                (
                    'Property not of expected type string: ' .
                    Fixtures\AbstractArrayBackedDaftObject::class .
                    '::$allows_null'
                ),
            ],
        ];
    }

    /**
    * @psalm-return array<int, array{0:scalar, 1:string|null}>
    */
    public function dataProvider_RetrievePropertyValueFromDataExpectArrayOrNull() : array
    {
        return [
            [
                1,
                (
                    'Property not of expected type array: ' .
                    Fixtures\AbstractArrayBackedDaftObject::class .
                    '::$allows_null'
                ),
            ],
        ];
    }

    /**
    * @psalm-return array<int, array{0:scalar, 1:string|null}>
    */
    public function dataProvider_RetrievePropertyValueFromDataExpectIntishOrNull() : array
    {
        return [
            [
                1.0,
                (
                    'Property not of expected type integer: ' .
                    Fixtures\AbstractArrayBackedDaftObject::class .
                    '::$allows_null'
                ),
            ],
            [
                1,
                null,
            ],
            [
                '1',
                null,
            ],
        ];
    }

    /**
    * @psalm-return array<int, array{0:scalar, 1:string|null}>
    */
    public function dataProvider_RetrievePropertyValueFromDataExpectFloatishOrNull() : array
    {
        return [
            [
                1,
                (
                    'Property not of expected type double: ' .
                    Fixtures\AbstractArrayBackedDaftObject::class .
                    '::$allows_null'
                ),
            ],
            [
                1.0,
                null,
            ],
            [
                '1.0',
                null,
            ],
        ];
    }

    /**
    * @psalm-return array<int, array{0:(scalar|scalar[]), 1:string|null}>
    */
    public function dataProvider_RetrievePropertyValueFromDataExpectBoolishOrNull() : array
    {
        return [
            [
                [],
                (
                    'Property not of expected type boolean: ' .
                    Fixtures\AbstractArrayBackedDaftObject::class .
                    '::$allows_null'
                ),
            ],
            [
                1,
                null,
            ],
            [
                '1',
                null,
            ],
            [
                0,
                null,
            ],
            [
                '0',
                null,
            ],
            [
                true,
                null,
            ],
            [
                false,
                null,
            ],
        ];
    }

    /**
    * @param scalar|array|object|null $val
    *
    * @dataProvider dataProvider_RetrievePropertyValueFromDataExpectStringOrNull
    */
    public function test_RetrievePropertyValueFromDataExpectStringOrNull(
        $val,
        ? string $exception_message
    ) : void {
        $obj = new Fixtures\AbstractArrayBackedDaftObject();

        static::assertNull($obj->public_RetrievePropertyValueFromDataExpectStringOrNull(
            'allows_null'
        ));

        $obj = new Fixtures\AbstractArrayBackedDaftObject([
            'allows_null' => $val,
        ]);

        if (is_string($exception_message)) {
            static::expectException(PropertyValueNotOfExpectedTypeException::class);
            static::expectExceptionMessage($exception_message);
        }

        $out = $obj->public_RetrievePropertyValueFromDataExpectStringOrNull('allows_null');

        if (is_null($exception_message)) {
            static::assertIsString($out);
        }
    }

    /**
    * @param scalar|array|object|null $val
    *
    * @dataProvider dataProvider_RetrievePropertyValueFromDataExpectArrayOrNull
    */
    public function test_RetrievePropertyValueFromDataExpectArrayOrNull(
        $val,
        ? string $exception_message
    ) : void {
        $obj = new Fixtures\AbstractArrayBackedDaftObject();

        static::assertNull($obj->public_RetrievePropertyValueFromDataExpectArrayOrNull(
            'allows_null'
        ));

        $obj = new Fixtures\AbstractArrayBackedDaftObject([
            'allows_null' => $val,
        ]);

        if (is_string($exception_message)) {
            static::expectException(PropertyValueNotOfExpectedTypeException::class);
            static::expectExceptionMessage($exception_message);
        }

        $out = $obj->public_RetrievePropertyValueFromDataExpectArrayOrNull('allows_null');

        if (is_null($exception_message)) {
            static::assertIsArray($out);
        }
    }

    /**
    * @param scalar|array|object|null $val
    *
    * @dataProvider dataProvider_RetrievePropertyValueFromDataExpectIntishOrNull
    */
    public function test_RetrievePropertyValueFromDataExpectIntishOrNull(
        $val,
        ? string $exception_message
    ) : void {
        $obj = new Fixtures\AbstractArrayBackedDaftObject();

        static::assertNull($obj->public_RetrievePropertyValueFromDataExpectIntishOrNull(
            'allows_null'
        ));

        $obj = new Fixtures\AbstractArrayBackedDaftObject([
            'allows_null' => $val,
        ]);

        if (is_string($exception_message)) {
            static::expectException(PropertyValueNotOfExpectedTypeException::class);
            static::expectExceptionMessage($exception_message);
        }

        $out = $obj->public_RetrievePropertyValueFromDataExpectIntishOrNull('allows_null');

        if (is_null($exception_message)) {
            static::assertIsInt($out);
        }
    }

    /**
    * @param scalar|array|object|null $val
    *
    * @dataProvider dataProvider_RetrievePropertyValueFromDataExpectFloatishOrNull
    */
    public function test_RetrievePropertyValueFromDataExpectFloatishOrNull(
        $val,
        ? string $exception_message
    ) : void {
        $obj = new Fixtures\AbstractArrayBackedDaftObject();

        static::assertNull($obj->public_RetrievePropertyValueFromDataExpectFloatishOrNull(
            'allows_null'
        ));

        $obj = new Fixtures\AbstractArrayBackedDaftObject([
            'allows_null' => $val,
        ]);

        if (is_string($exception_message)) {
            static::expectException(PropertyValueNotOfExpectedTypeException::class);
            static::expectExceptionMessage($exception_message);
        }

        $out = $obj->public_RetrievePropertyValueFromDataExpectFloatishOrNull('allows_null');

        if (is_null($exception_message)) {
            static::assertIsFloat($out);
        }
    }

    /**
    * @param scalar|array|object|null $val
    *
    * @dataProvider dataProvider_RetrievePropertyValueFromDataExpectBoolishOrNull
    */
    public function test_RetrievePropertyValueFromDataExpectBoolishOrNull(
        $val,
        ? string $exception_message
    ) : void {
        $obj = new Fixtures\AbstractArrayBackedDaftObject();

        static::assertNull($obj->public_RetrievePropertyValueFromDataExpectBoolishOrNull(
            'allows_null'
        ));

        $obj = new Fixtures\AbstractArrayBackedDaftObject([
            'allows_null' => $val,
        ]);

        if (is_string($exception_message)) {
            static::expectException(PropertyValueNotOfExpectedTypeException::class);
            static::expectExceptionMessage($exception_message);
        }

        $out = $obj->public_RetrievePropertyValueFromDataExpectBoolishOrNull('allows_null');

        if (is_null($exception_message)) {
            static::assertIsBool($out);
        }
    }
}
