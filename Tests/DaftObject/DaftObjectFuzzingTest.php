<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use DateTimeImmutable;
use Generator;
use ReflectionClass;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\AbstractTestObject;
use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectWorm;
use SignpostMarv\DaftObject\DateTimeImmutableTestObject;
use SignpostMarv\DaftObject\Exceptions\DaftObjectNotDaftJsonBadMethodCallException;
use SignpostMarv\DaftObject\Exceptions\PropertyNotNullableException;
use SignpostMarv\DaftObject\Exceptions\PropertyNotRewriteableException;
use SignpostMarv\DaftObject\LinkedData\HasArrayOfHasId;
use SignpostMarv\DaftObject\LinkedData\HasId;
use SignpostMarv\DaftObject\PasswordHashTestObject;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @template T as DaftObject
*/
class DaftObjectFuzzingTest extends TestCase
{
    const NUM_EXPECTED_ARGS_FOR_IMPLEMENTATION = 5;

    const REGEX_PSALM_GETTER_TYPE = '/\* @(psalm\-return) (.+)\n/';

    const REGEX_GETTER_TYPE = '/\* @(return) (.+)\n/';

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzing() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            foreach ($this->FuzzingImplementationsViaGenerator() as $fuzzingImplementationArgs) {
                if (is_a($args[0], $fuzzingImplementationArgs[0], true)) {
                    /**
                    * @psalm-var class-string<T>
                    */
                    $args[0] = $args[0];

                    $getters = [];
                    $setters = [];

                    $properties = $args[0]::DaftObjectProperties();

                    $initialCount = count($properties);

                    if (
                        $initialCount !== count(
                            array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR)
                        )
                    ) {
                        continue;
                    }

                    foreach ($properties as $property) {
                        $propertyForMethod = ucfirst($property);
                        $getter = TypeUtilities::MethodNameFromProperty($propertyForMethod, false);
                        $setter = TypeUtilities::MethodNameFromProperty($propertyForMethod, true);

                        if (
                            $args[1]->hasMethod($getter) &&
                            $args[1]->getMethod($getter)->isPublic()
                        ) {
                            $getters[] = $property;
                        }

                        if (
                            $args[1]->hasMethod($setter) &&
                            $args[1]->getMethod($setter)->isPublic()
                        ) {
                            $setters[] = $property;
                        }
                    }

                    yield [$args[0], $args[1], $fuzzingImplementationArgs[1], $getters, $setters];
                }
            }
        }
    }

    /**
    * @param array<string, scalar|array|object|null> $args
    * @param array<int, string> $getters
    * @param array<int, string> $setters
    *
    * @psalm-param class-string<T> $className
    *
    * @dataProvider dataProviderNonAbstractNonWormGoodFuzzingHasSetters
    */
    final public function testProviderNonAbstractGoodFuzzingSetFromBlank(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        $obj = new $className($args);

        static::assertCount(
            0,
            $obj->ChangedProperties(),
            ($className . '::ChangedProperties() must be empty after instantiation')
        );

        $obj = new $className([]);

        static::assertCount(
            0,
            $obj->ChangedProperties(),
            ($className . '::ChangedProperties() must be empty after instantiation')
        );

        $otherProperties = $className::DaftObjectPropertiesChangeOtherProperties();

        foreach ($setters as $setterProperty) {
            $propertiesExpectedToBeChanged = [
                $setterProperty,
            ];

            $propertiesExpectedNotToBeChanged = $className::DaftObjectProperties();

            if (isset($otherProperties[$setterProperty])) {
                $propertiesExpectedToBeChanged = $otherProperties[$setterProperty];
                $propertiesExpectedNotToBeChanged = array_filter(
                    $propertiesExpectedNotToBeChanged,
                    function (string $maybe) use ($otherProperties, $setterProperty) : bool {
                        return ! in_array($maybe, $otherProperties[$setterProperty], true);
                    }
                );
            }

            /**
            * @var array<int, string>
            */
            $propertiesExpectedNotToBeChanged = array_filter(
                $propertiesExpectedNotToBeChanged,
                function (string $maybe) use ($propertiesExpectedToBeChanged) : bool {
                    return ! in_array($maybe, $propertiesExpectedToBeChanged, true);
                }
            );

            $checkingProperties = array_merge(
                $propertiesExpectedToBeChanged,
                $propertiesExpectedNotToBeChanged
            );

            foreach (
                $checkingProperties as $property
            ) {
                static::assertFalse(
                    $obj->HasPropertyChanged($property),
                    (
                        $className .
                        '::$' .
                        $property .
                        ' should not be marked as changed' .
                        ' when instantiating from blank.'
                    )
                );
            }

            if (isset($args[$setterProperty])) {
                $obj->__set($setterProperty, $args[$setterProperty]);

                foreach ($propertiesExpectedToBeChanged as $property) {
                    static::assertTrue(
                        $obj->HasPropertyChanged($property),
                        ($className . '::$' . $property . ' should be marked as changed.')
                    );
                }

                foreach ($propertiesExpectedNotToBeChanged as $property) {
                    static::assertFalse(
                        $obj->HasPropertyChanged($property),
                        ($className . '::$' . $property . ' should not be marked as changed.')
                    );
                }
            }

            $obj->MakePropertiesUnchanged(...$checkingProperties);

            foreach (
                $checkingProperties as $property
            ) {
                static::assertFalse(
                    $obj->HasPropertyChanged($property),
                    (
                        $className .
                        '::$' .
                        $property .
                        ' should be marked as unchanged after calling ' .
                        $className .
                        '::MakePropertiesUnchanged()'
                    )
                );
            }
        }

        $obj = new $className([]);

        $propertiesExpectedToBeChanged = [];

        foreach ($setters as $property) {
            if ( ! isset($args[$property])) {
                continue;
            }

            $obj->__set($property, $args[$property]);

            if (isset($otherProperties[$property])) {
                $propertiesExpectedToBeChanged = array_merge(
                    $propertiesExpectedToBeChanged,
                    $otherProperties[$property]
                );
            } else {
                $propertiesExpectedToBeChanged[] = $property;
            }

            if (in_array($property, $getters, true)) {
                /**
                * @var scalar|array|object|null
                */
                $expecting = $args[$property];

                $compareTo = $obj->__get($property);

                if (
                    ($expecting !== $compareTo) &&
                    ($expecting instanceof DateTimeImmutable) &&
                    ($compareTo instanceof DateTimeImmutable) &&
                    get_class($expecting) === get_class($compareTo)
                ) {
                    $expecting = $expecting->format('cu');
                    $compareTo = $compareTo->format('cu');
                }

                static::assertSame($expecting, $compareTo);
            }
        }

        foreach ($propertiesExpectedToBeChanged as $property) {
            static::assertTrue(
                in_array($property, $obj->ChangedProperties(), true),
                ($className . '::ChangedProperties() must contain changed properties')
            );
        }

        $properties = $className::DaftObjectNullableProperties();

        foreach ($properties as $property) {
            $checkGetterIsNull = (
                in_array($property, $getters, true) &&
                array_key_exists($property, $args) &&
                false === is_null($args[$property])
            );

            if ($obj->HasPropertyChanged($property)) {
                if ($checkGetterIsNull) {
                    static::assertTrue(
                        $obj->__isset($property),
                        (
                            $className .
                            '::__isset(' .
                            $property .
                            ') must return true after ' .
                            $className .
                            '::$' .
                            $property .
                            ' has been set'
                        )
                    );
                }

                $obj->__unset($property);
            }

            if ($checkGetterIsNull) {
                static::assertNull(
                    $obj->__get($property),
                    ($className . '::$' . $property . ' must be null after being unset')
                );
            }
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSetters
    *
    * @psalm-param class-string<T> $className
    */
    final public function testProviderNonAbstractGoodFuzzingSetFromBlankThenJsonSerialiseMaybeFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        $obj = new $className($args);

        if ($obj instanceof DaftJson) {
            /**
            * @psalm-var class-string<DaftJson>
            */
            $className = $className;

            $obj->jsonSerialize();

            $json = json_encode($obj);

            static::assertIsString(
                $json,
                (
                    'Instances of ' .
                    get_class($obj) .
                    ' should resolve to a string when passed to json_encode()'
                )
            );

            /**
            * @var array|false
            */
            $decoded = json_decode($json, true);

            static::assertIsArray(
                $decoded,
                (
                    'JSON-encoded implementations of ' .
                    DaftJson::class .
                    ' (' .
                    get_class($obj) .
                    ')' .
                    ' must decode to an array!'
                )
            );

            /**
            * @var array<int|string, scalar|(scalar|array|object|null)[]|object|null>
            */
            $decoded = $decoded;

            $objFromJson = $className::DaftObjectFromJsonArray($decoded);

            static::assertSame(
                $json,
                json_encode($objFromJson),
                (
                    'JSON-encoded implementations of ' .
                    DaftJson::class .
                    ' must encode($obj) the same as encode(decode($str))'
                )
            );

            $objFromJson = $className::DaftObjectFromJsonString($json);

            static::assertSame(
                $json,
                json_encode($objFromJson),
                (
                    'JSON-encoded implementations of ' .
                    DaftJson::class .
                    ' must encode($obj) the same as encode(decode($str))'
                )
            );
        } else {
            if (method_exists($obj, 'jsonSerialize')) {
                $this->expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
                $this->expectExceptionMessage(sprintf(
                    '%s does not implement %s',
                    $className,
                    DaftJson::class
                ));

                $obj->jsonSerialize();

                return;
            }
            static::markTestSkipped(sprintf(
                '%s does not implement %s or %s::jsonSerialize()',
                $className,
                DaftJson::class,
                $className
            ));

            return;
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters
    *
    * @psalm-param class-string<AbstractArrayBackedDaftObject> $className
    */
    final public function testProviderNonAbstractGoodFuzzingJsonFromArrayFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        $this->expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftJson::class
        ));

        $className::DaftObjectFromJsonArray([]);
    }

    /**
    * @dataProvider dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters
    *
    * @psalm-param class-string<DaftObject> $className
    *
    * @psalm-suppress UndefinedMethod
    */
    final public function testProviderNonAbstractGoodFuzzingJsonFromStringFailure(
        string $className
    ) : void {
        $this->expectException(DaftObjectNotDaftJsonBadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            '%s does not implement %s',
            $className,
            DaftJson::class
        ));

        $className::DaftObjectFromJsonString('{}');
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzing() as $args) {
            if (count((array) $args[4]) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftJson>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSetters_DaftJson() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            if (is_a($args[0], DaftJson::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftObject>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSetters_Not_DaftJson() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            if ( ! is_a($args[0], DaftJson::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractNonWormGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            if ( ! is_a($args[0], DaftObjectWorm::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<AbstractArrayBackedDaftObject>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
    */
    final public function dataProviderNonAbstractJsonArrayBackedGoodFuzzingHasSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            if (
                false === is_a($args[0], DaftJson::class, true) &&
                is_a($args[0], AbstractArrayBackedDaftObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>, 5:string}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSetters() as $args) {
            foreach ($args[4] as $property) {
                if (in_array($property, array_keys((array) $args[2]), true)) {
                    yield [$args[0], $args[1], $args[2], $args[3], $args[4], $property];
                }
            }
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSetters_Not_DaftJson
    *
    * @depends testProviderNonAbstractGoodFuzzingJsonFromStringFailure
    *
    * @psalm-param class-string<DaftObject> $className
    */
    final public function testProviderNonAbstractGoodFuzzingJsonFromStringFailure_dataProviderNonAbstractGoodFuzzingHasSetters(
        string $className
    ) : void {
        $this->testProviderNonAbstractGoodFuzzingJsonFromStringFailure(
            $className
        );
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSetters_DaftJson
    *
    * @psalm-param class-string<DaftJson> $className
    */
    final public function testProviderNonAbstractGoodFuzzingSetFromBlankThenJsonSerialiseMaybePropertiesFailure(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters
    ) : void {
        $exportables = (array) $className::DaftObjectExportableProperties();

        $propertyNames = (array) $className::DaftObjectJsonPropertyNames();

        $jsonProps = [];

        $properties = $className::DaftObjectJsonProperties();

        foreach ($properties as $k => $v) {
            $prop = $v;

            if (is_string($k)) {
                if ('[]' === mb_substr($v, -2)) {
                    $v = mb_substr($v, 0, -2);
                }

                static::assertTrue(
                    class_exists($v),
                    sprintf(
                        (
                            'When %s::DaftObjectJsonProperties()' .
                            ' ' .
                            'key-value pair is array<string, string>' .
                            ' ' .
                            'the value must refer to a class.'
                        ),
                        $className
                    )
                );

                static::assertTrue(
                    is_a($v, DaftJson::class, true),
                    sprintf(
                        (
                            'When %s::DaftObjectJsonProperties()' .
                            ' ' .
                            'key-value pair is array<string, string>' .
                            ' ' .
                            'the value must be an implementation of %s'
                        ),
                        $className,
                        DaftJson::class
                    )
                );

                $prop = $k;
            }

            static::assertContains(
                $prop,
                $exportables,
                sprintf(
                    (
                        'Properties listed in' .
                        ' ' .
                        '%s::DaftObjectJsonProperties() must also be' .
                        ' ' .
                        'listed in %s::DaftObjectExportableProperties()'
                    ),
                    $className,
                    $className
                )
            );

            static::assertContains(
                $prop,
                $propertyNames,
                sprintf(
                    (
                        'Properties listed in' .
                        ' ' .
                        '%s::DaftObjectJsonProperties() must also be' .
                        ' ' .
                        'listed in %s::DaftObjectJsonPropertyNames()'
                    ),
                    $className,
                    $className
                )
            );

            $jsonProps[] = $prop;
        }

        foreach ($propertyNames as $prop) {
            static::assertContains(
                $prop,
                $propertyNames,
                sprintf(
                    (
                        'Properties listed in' .
                        ' ' .
                        '%s::DaftObjectJsonPropertyNames() must also be' .
                        ' ' .
                        'listed or referenced in' .
                        ' ' .
                        '%s::DaftObjectJsonProperties()'
                    ),
                    $className,
                    $className
                )
            );
        }

        $initialCount = count($propertyNames);

        static::assertCount(
            $initialCount,
            array_unique(
                array_map('mb_strtolower', $propertyNames),
                SORT_REGULAR
            )
        );
    }

    /**
    * @param array<string, scalar|array|object|null> $args
    *
    * @psalm-param class-string<T> $className
    *
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm
    */
    final public function testProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters,
        string $property
    ) : void {
        $obj = new $className($args, true);

        $this->expectException(PropertyNotRewriteableException::class);
        $this->expectExceptionMessage(
            'Property not rewriteable: ' .
            $className .
            '::$' .
            $property
        );

        $obj->__set($property, $args[$property]);
    }

    /**
    * @param array<string, scalar|array|object|null> $args
    *
    * @psalm-param class-string<T> $className
    *
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm
    */
    final public function testProviderNonAbstractGoodFuzzingHasSettersPerPropertyWormAfterCreate(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters,
        string $property
    ) : void {
        $obj = new $className([], true);

        $obj->__set($property, $args[$property]);

        $this->expectException(PropertyNotRewriteableException::class);
        $this->expectExceptionMessage(
            'Property not rewriteable: ' .
            $className .
            '::$' .
            $property
        );

        $obj->__set($property, $args[$property]);
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable
    *
    * @psalm-param class-string<DaftObject> $className
    */
    final public function testNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable(
        string $className,
        ReflectionClass $reflector,
        array $args,
        array $getters,
        array $setters,
        string $property
    ) : void {
        if (
            ! $reflector->isAbstract() &&
            in_array($property, $setters, true)
        ) {
            $method = $reflector->getMethod('NudgePropertyValue');

            $method->setAccessible(true);

            $this->expectException(PropertyNotNullableException::class);
            $this->expectExceptionMessage(sprintf(
                'Property not nullable: %s::$%s',
                $className,
                $property
            ));

            $method->invoke(new $className(), $property, null);
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&DaftObjectWorm>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>, 5:string}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyWorm() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() as $args) {
            if (is_a($args[0], DaftObjectWorm::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftObject>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>, 5:string}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodFuzzingHasSettersPerPropertyNotNullable(
    ) : Generator {
        foreach ($this->dataProviderNonAbstractGoodFuzzingHasSettersPerProperty() as $args) {
            if ( ! in_array($args[5], $args[0]::DaftObjectNullableProperties(), true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return array<int, array{0:class-string<T>, 1:array<string, scalar|array|object|null>}>
    */
    protected function FuzzingImplementationsViaArray() : array
    {
        return [
            [
                AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
            ],
            [
                AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
            ],
            [
                AbstractTestObject::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
            ],
            [
                PasswordHashTestObject::class,
                [
                    'password' => 'foo',
                ],
            ],
            [
                DateTimeImmutableTestObject::class,
                [
                    'datetime' => new DateTimeImmutable(date(
                        DateTimeImmutableTestObject::STR_FORMAT_TEST,
                        0
                    )),
                ],
            ],
            [
                DateTimeImmutableTestObject::class,
                [
                    'datetime' => new DateTimeImmutable(date(
                        DateTimeImmutableTestObject::STR_FORMAT_TEST,
                        1
                    )),
                ],
            ],
            [
                HasArrayOfHasId::class,
                [
                    'json' => [],
                ],
            ],
            [
                HasArrayOfHasId::class,
                [
                    'json' => [],
                    'single' => null,
                ],
            ],
            [
                HasArrayOfHasId::class,
                [
                    'json' => [],
                    'single' => new HasId(['@id' => 'foo']),
                ],
            ],
            [
                HasArrayOfHasId::class,
                [
                    'json' => [new HasId(['@id' => 'foo'])],
                    'single' => new HasId(['@id' => 'foo']),
                ],
            ],
            [
                HasArrayOfHasId::class,
                [
                    'json' => [new HasId(['@id' => 'foo'])],
                    'single' => new HasId(['@id' => 'bar']),
                ],
            ],
        ];
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:array<string, scalar|array|object|null>}, mixed, void>
    */
    protected function FuzzingImplementationsViaGenerator() : Generator
    {
        yield from $this->FuzzingImplementationsViaArray();
    }
}
