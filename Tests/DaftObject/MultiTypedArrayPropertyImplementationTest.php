<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use DateTime;
use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DefinitionAssistant;
use SignpostMarv\DaftObject\MultiTypedArrayPropertiesTester;
use SignpostMarv\DaftObject\Tests\TestCase;

class MultiTypedArrayPropertyImplementationTest extends TestCase
{
    /**
    * @psalm-return Generator<int, array{0: MultiTypedArrayPropertiesTester, 1:string, 2:scalar|array|object|null, 3:class-string<\Throwable>, 4:string}, mixed, void>
    */
    public function DataProviderObjectPropertyValueException() : Generator
    {
        yield from [
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                0,
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    DefinitionAssistant::class .
                    '::MaybeThrowIfValueDoesNotMatchMultiTypedArray must be an array' .
                    ', integer given!'
                ),
            ],
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                ['foo' => 'bar'],
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    DefinitionAssistant::class .
                    '::MaybeThrowIfNotArrayIntKeys must be array<int, mixed>'
                ),
            ],
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                [new DateTime()],
                InvalidArgumentException::class,
                (
                    'Argument 3 passed to ' .
                    DefinitionAssistant::class .
                    '::MaybeThrowIfValueArrayDoesNotMatchTypes' .
                    ' contained values that did not match the provided types!'
                ),
            ],
        ];
    }

    /**
    * @psalm-return Generator<int, array{0:MultiTypedArrayPropertiesTester, 1:string, 2:array<int, mixed>}, mixed, void>
    */
    public function DataProviderObjectPropertyValueNotUniqueAutoDouble() : Generator
    {
        foreach ($this->DataProviderObjectPropertyValueNotUnique() as $args) {
            $args_2 = $args[2] ?? null;

            static::assertIsArray($args_2);

            $args[2] = array_merge(array_values($args_2), array_values($args_2));

            /**
            * @psalm-var array{0:MultiTypedArrayPropertiesTester, 1:string, 2:array<int, mixed>}
            */
            $args = $args;

            yield $args;
        }
    }

    /**
    * @psalm-return Generator<int, array{0:MultiTypedArrayPropertiesTester, 1:string, 2:scalar|array|object|null, 3:scalar|array|object|null}, mixed, void>
    */
    public function DataProviderObjectPropertyValueTrimmedStrings() : Generator
    {
        yield from [
            [
                new MultiTypedArrayPropertiesTester(),
                'trimmedStrings',
                [' foo', 'foo', 'foo ', ' foo '],
                ['foo'],
            ],
            [
                new MultiTypedArrayPropertiesTester(),
                'trimmedString',
                ' foo ',
                'foo',
            ],
        ];
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<\Throwable> $expectedException
    *
    * @dataProvider DataProviderObjectPropertyValueException
    */
    public function testNudgingThrows(
        DaftObject $obj,
        string $property,
        $value,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        static::expectException($expectedException);
        static::expectExceptionMessage($expectedExceptionMessage);

        $obj->__set($property, $value);
    }

    /**
    * @dataProvider DataProviderObjectPropertyValueNotUniqueAutoDouble
    */
    public function testNonUniqueThrows(
        DaftObject $obj,
        string $property,
        array $value
    ) : void {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 3 passed to ' .
            DefinitionAssistant::class .
            '::MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray contained non-unique values!'
        );

        $obj->__set($property, $value);
    }

    /**
    * @param scalar|array|object|null $value
    * @param scalar|array|object|null $expected
    *
    * @dataProvider DataProviderObjectPropertyValueTrimmedStrings
    */
    public function testAutoTrimmedStrings(
        DaftObject $obj,
        string $property,
        $value,
        $expected
    ) : void {
        $obj->__set($property, $value);

        static::assertSame($expected, $obj->__get($property));
    }

    /**
    * @psalm-return Generator<int, array{0:MultiTypedArrayPropertiesTester, 1:string, 2:scalar|array|object|null}, mixed, void>
    */
    protected function DataProviderObjectPropertyValueNotUnique() : Generator
    {
        yield from [
            [
                new MultiTypedArrayPropertiesTester(),
                'dates',
                [new DateTimeImmutable()],
            ],
        ];
    }
}
