<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use DateTimeImmutable;
use Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\AbstractTestObject;
use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectCreatedByArray;
use SignpostMarv\DaftObject\DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues;
use SignpostMarv\DaftObject\DaftObjectWorm;
use SignpostMarv\DaftObject\DaftSortableObject;
use SignpostMarv\DaftObject\DateTimeImmutableTestObject;
use SignpostMarv\DaftObject\DefinesOwnArrayIdInterface;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;
use SignpostMarv\DaftObject\DefinesOwnStringIdInterface;
use SignpostMarv\DaftObject\Exceptions\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\Exceptions\DaftObjectNotDaftJsonBadMethodCallException;
use SignpostMarv\DaftObject\Exceptions\NotPublicGetterPropertyException;
use SignpostMarv\DaftObject\Exceptions\PropertyNotNullableException;
use SignpostMarv\DaftObject\Exceptions\PropertyNotRewriteableException;
use SignpostMarv\DaftObject\Exceptions\UndefinedPropertyException;
use SignpostMarv\DaftObject\LinkedData\HasArrayOfHasId;
use SignpostMarv\DaftObject\LinkedData\HasId;
use SignpostMarv\DaftObject\LinkedData\HasIdPublicNudge;
use SignpostMarv\DaftObject\PasswordHashTestObject;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\TypeUtilities;
use SignpostMarv\DaftObject\WriteOnly;

/**
* @template T as DaftObject
*/
class DaftObjectImplementationTest extends TestCase
{
    const NUM_EXPECTED_ARGS_FOR_IMPLEMENTATION = 5;

    const REGEX_PSALM_GETTER_TYPE = '/\* @(psalm\-return) (.+)\n/';

    const REGEX_GETTER_TYPE = '/\* @(return) (.+)\n/';

    /**
    * @dataProvider dataProviderNonAbstractGoodImplementationsWithProperties
    *
    * @psalm-param class-string<T> $className
    */
    final public function testHasDefinedAllPropertiesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        $properties = $className::DaftObjectProperties();

        static::assertGreaterThan(0, count($properties));
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodImplementationsWithMixedCaseProperties
    *
    * @depends testHasDefinedAllPropertiesCorrectly
    *
    * @psalm-param class-string<DaftObject> $className
    */
    final public function testHasDefinedAllPropertiesCorrectlyExceptMixedCase(
        string $className,
        ReflectionClass $reflector,
        bool $hasMixedCase
    ) : void {
        $properties = $className::DaftObjectProperties();

        $initialCount = count($properties);
        $postCount = count(array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR));

        if ($hasMixedCase) {
            static::assertLessThan($initialCount, $postCount);
        } else {
            static::assertSame($initialCount, $postCount);
        }
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodNullableImplementations
    *
    * @depends testHasDefinedAllPropertiesCorrectly
    *
    * @psalm-param class-string<T> $className
    */
    final public function testHasDefinedAllNullablesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        $nullables = $className::DaftObjectNullableProperties();

        $properties = $className::DaftObjectProperties();

        foreach ($nullables as $nullable) {
            static::assertTrue(
                in_array($nullable, $properties, true),
                (
                    $className .
                    '::DaftObjectNullableProperties()' .
                    ' ' .
                    'a nullable property (' .
                    $nullable .
                    ') that was not defined as a property on ' .
                    $className .
                    '::DaftObjectProperties()'
                )
            );
        }

        if (count($properties) > 0 && 0 === count($nullables)) {
            foreach ($properties as $property) {
                $getter = TypeUtilities::MethodNameFromProperty($property, false);
                $setter = TypeUtilities::MethodNameFromProperty($property, true);

                if ($reflector->hasMethod($getter)) {
                    $method = $reflector->getMethod($getter);

                    static::assertTrue(
                        $method->hasReturnType(),
                        (
                            $method->getDeclaringClass()->getName() .
                            '::' .
                            $getter .
                            ' had no return type, cannot verify is not nullable.'
                        )
                    );

                    /**
                    * @var ReflectionType
                    */
                    $returnType = $method->getReturnType();

                    static::assertFalse(
                        $returnType->allowsNull(),
                        (
                            $method->getDeclaringClass()->getName() .
                            '::' .
                            $getter .
                            ' defines a nullable return type, but ' .
                            $className .
                            ' indicates no nullable properties!'
                        )
                    );
                }
                if ($reflector->hasMethod($setter)) {
                    $method = $reflector->getMethod($setter);

                    static::assertGreaterThan(
                        0,
                        $method->getNumberOfParameters(),
                        (
                            $method->getDeclaringClass()->getName() .
                            '::' .
                            $setter .
                            ' has no parameters, cannot verify is not nullable!'
                        )
                    );

                    foreach ($method->getParameters() as $param) {
                        static::assertFalse(
                            $param->allowsNull(),
                            (
                                $method->getDeclaringClass()->getName() .
                                '::' .
                                $setter .
                                ' defines a parameter that allows null, but ' .
                                $className .
                                ' indicates no nullable properties!'
                            )
                        );
                    }
                }
            }
        }

        $initialCount = count($nullables);

        static::assertCount(
            $initialCount,
            array_unique(
                array_map('mb_strtolower', $nullables),
                SORT_REGULAR
            )
        );
    }

    /**
    * @dataProvider dataProviderNonAbstractGoodExportableImplementations
    *
    * @depends testHasDefinedAllPropertiesCorrectly
    *
    * @psalm-param class-string<T> $className
    */
    final public function testHasDefinedAllExportablesCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        $exportables = $className::DaftObjectExportableProperties();

        $properties = $className::DaftObjectProperties();

        foreach ($exportables as $exportable) {
            static::assertTrue(
                in_array($exportable, $properties, true),
                (
                    $className .
                    '::DaftObjectNullableProperties()' .
                    ' ' .
                    'a nullable property (' .
                    $exportable .
                    ') that was not defined as a property on ' .
                    $className .
                    '::DaftObjectProperties()'
                )
            );
        }

        if (0 === count($exportables) && count($properties) > 0) {
            static::assertFalse(
                is_a($className, DaftJson::class, true),
                (
                    'Implementations with no exportables should not implement ' .
                    DaftJson::class
                )
            );
        }

        $initialCount = count($exportables);

        static::assertCount(
            $initialCount,
            array_unique(
                array_map('mb_strtolower', $exportables),
                SORT_REGULAR
            )
        );
    }

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
    * @dataProvider dataProviderNonAbstractGoodPropertiesImplementations
    *
    * @psalm-param class-string<T> $className
    */
    final public function testHasDefinedImplementationCorrectly(
        string $className,
        ReflectionClass $reflector
    ) : void {
        $properties = $className::DaftObjectProperties();

        $nullables = $className::DaftObjectNullableProperties();

        $exportables = $className::DaftObjectExportableProperties();

        foreach ($properties as $property) {
            $getter = TypeUtilities::MethodNameFromProperty($property, false);
            $setter = TypeUtilities::MethodNameFromProperty($property, true);

            $hasAny = $reflector->hasMethod($getter) || $reflector->hasMethod($setter);

            static::assertTrue(
                $hasAny,
                (
                    $className .
                    ' must implement at least a getter `' .
                    $className .
                    '::' .
                    $getter .
                    '()` or setter `' .
                    $className .
                    '::' .
                    $setter .
                    '()` for ' .
                    $property .
                    ' on ' .
                    $className .
                    '.'
                )
            );

            $reflectorGetter = null;

            try {
                $reflectorGetter = $reflector->getMethod($getter);
            } catch (ReflectionException $e) {
            }

            $getterPublic = (
                ($reflectorGetter instanceof ReflectionMethod) &&
                $reflectorGetter->isPublic()
            );

            if (($reflectorGetter instanceof ReflectionMethod) && ! $reflectorGetter->isPublic()) {
                $reflectorGetter = null;
            }

            $reflectorSetter = null;

            try {
                $reflectorSetter = $reflector->getMethod($setter);
            } catch (ReflectionException $e) {
            }

            $setterPublic = (
                ($reflectorSetter instanceof ReflectionMethod) &&
                $reflectorSetter->isPublic()
            );

            if (($reflectorGetter instanceof ReflectionMethod) && ! $reflectorGetter->isPublic()) {
                $reflectorGetter = null;
            }

            $anyPublic = $getterPublic || $setterPublic;

            $isNullable = in_array($property, $nullables, true);

            static::assertTrue(
                $anyPublic,
                (
                    $className .
                    ' must implement at least a public getter or setter for ' .
                    $className .
                    '::$' .
                    $property
                )
            );

            if ($reflectorGetter instanceof ReflectionMethod) {
                static::assertSame(
                    0,
                    $reflectorGetter->getNumberOfParameters(),
                    (
                        $reflectorGetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorGetter->getName() .
                        '() must not have any parameters.'
                    )
                );

                if (
                    'id' !== $property ||
                    is_a($className, DefinesOwnArrayIdInterface::class, true) ||
                    is_a($className, DefinesOwnIntegerIdInterface::class, true) ||
                    is_a($className, DefinesOwnStringIdInterface::class, true)
                ) {
                    static::assertTrue(
                        $reflectorGetter->hasReturnType(),
                        (
                            $reflectorGetter->getNumberOfParameters() .
                            $reflectorGetter->getDeclaringClass()->getName() .
                            '::' .
                            $reflectorGetter->getName() .
                            '() must have a return type.'
                        )
                    );
                }

                if ($reflectorGetter->hasReturnType()) {
                    /**
                    * @var ReflectionType
                    */
                    $returnType = $reflectorGetter->getReturnType();

                    static::assertTrue(
                        ('void' !== $returnType->__toString()),
                        (
                            $reflectorGetter->getNumberOfParameters() .
                            $reflectorGetter->getDeclaringClass()->getName() .
                            '::' .
                            $reflectorGetter->getName() .
                            '() must have a non-void return type.'
                        )
                    );

                    if ($isNullable) {
                        static::assertTrue(
                            $returnType->allowsNull(),
                            (
                                $reflectorGetter->getNumberOfParameters() .
                                $reflectorGetter->getDeclaringClass()->getName() .
                                '::' .
                                $reflectorGetter->getName() .
                                '() must have a nullable return type.'
                            )
                        );
                    }
                }
            }

            if ($reflectorSetter instanceof ReflectionMethod) {
                static::assertSame(
                    1,
                    $reflectorSetter->getNumberOfParameters(),
                    (
                        $reflectorSetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorSetter->getName() .
                        '() must have only one parameter.'
                    )
                );

                static::assertTrue(
                    $reflectorSetter->hasReturnType(),
                    (
                        $reflectorSetter->getNumberOfParameters() .
                        $reflectorSetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorSetter->getName() .
                        '() must specify a void return type.'
                    )
                );

                /**
                * @var ReflectionType
                */
                $returnType = $reflectorSetter->getReturnType();

                static::assertSame(
                    'void',
                    $returnType->__toString(),
                    (
                        $reflectorSetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorSetter->getName() .
                        '() must specify a void return type, "' .
                        $returnType->__toString() .
                        '" found.'
                    )
                );

                $type = ($reflectorSetter->getParameters()[0])->getType();

                if ($type instanceof ReflectionType) {
                    static::assertSame(
                        $type->allowsNull(),
                        $isNullable,
                        (
                            $reflectorSetter->getDeclaringClass()->getName() .
                            '::' .
                            $reflectorSetter->getName() .
                            '() must have a ' .
                            ($isNullable ? '' : 'non-') .
                            'nullable type when specified.'
                        )
                    );
                }
            }
        }

        $propertiesChangeProperties = $className::DaftObjectPropertiesChangeOtherProperties();

        $propertiesChangePropertiesCount = count($propertiesChangeProperties);

        $propertiesChangeProperties = array_filter(
            array_filter(
                array_filter($propertiesChangeProperties, 'is_string', ARRAY_FILTER_USE_KEY),
                'is_array'
            ),
            function (string $maybe) use ($properties) : bool {
                return in_array($maybe, $properties, true);
            },
            ARRAY_FILTER_USE_KEY
        );

        static::assertCount($propertiesChangePropertiesCount, $propertiesChangeProperties);

        $propertiesChangePropertiesCount = count($propertiesChangeProperties, COUNT_RECURSIVE);

        $propertiesChangeProperties = array_map(
            function (array $in) use ($properties) : array {
                return array_values(array_unique(array_filter(
                    array_filter(
                        array_filter(
                            $in,
                            'is_string'
                        ),
                        'is_int',
                        ARRAY_FILTER_USE_KEY
                    ),
                    function (string $property) use ($properties) : bool {
                        return in_array($property, $properties, true);
                    }
                )));
            },
            $propertiesChangeProperties
        );

        static::assertTrue(
            $propertiesChangePropertiesCount === count(
                $propertiesChangeProperties,
                COUNT_RECURSIVE
            )
        );
    }

    /**
    * @dataProvider dataProviderGoodNonAbstractGetterSettersNotId
    *
    * @depends testHasDefinedImplementationCorrectly
    *
    * @psalm-param class-string<T> $className
    */
    final public function testHasAllGettersAndSettersDefinedAsProperties(
        string $className,
        ReflectionMethod $reflector
    ) : void {
        $property = (string) preg_replace('/^(?:[GS]et|Obtain|Alter)/', '', $reflector->getName());

        $properties = $className::DaftObjectProperties();

        $defined = (
            in_array($property, $properties, true) ||
            in_array(lcfirst($property), $properties, true)
        );

        static::assertTrue(
            $defined,
            (
                $reflector->getDeclaringClass()->getName() .
                '::' .
                $reflector->getName() .
                '() was not defined in ' .
                $className .
                '::DaftObjectProperties()'
            )
        );
    }

    /**
    * @param array<string, scalar|array|object|null> $args
    * @param array<int, string> $getters
    * @param array<int, string> $setters
    *
    * @psalm-param class-string<T> $className
    *
    * @dataProvider dataProviderNonAbstractNonWormGoodFuzzingHasSetters
    *
    * @depends testHasDefinedImplementationCorrectly
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

        $debugInfo = $this->VarDumpDaftObject($obj);

        $regex = '/' . static::RegexForObject($obj) . '$/s';

        static::assertRegExp($regex, str_replace(["\n"], ' ', $debugInfo));

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
    * @depends testHasDefinedImplementationCorrectly
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
    * @depends testHasDefinedImplementationCorrectly
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
    * @depends testHasDefinedImplementationCorrectly
    *
    * @psalm-param class-string<AbstractArrayBackedDaftObject> $className
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
    * @psalm-return Generator<int, array{0:class-string<T&DaftJson>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
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
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
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
    * @psalm-return Generator<int, array{0:class-string<T&AbstractArrayBackedDaftObject>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>}, mixed, void>
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
    * @depends testHasDefinedAllExportablesCorrectly
    * @depends testHasDefinedImplementationCorrectly
    * @depends testProviderNonAbstractGoodFuzzingJsonFromStringFailure
    *
    * @psalm-param class-string<AbstractArrayBackedDaftObject> $className
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
    * @depends testHasDefinedAllExportablesCorrectly
    * @depends testHasDefinedImplementationCorrectly
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
    *
    * @depends testHasDefinedImplementationCorrectly
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
    *
    * @depends testHasDefinedImplementationCorrectly
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
    * @psalm-param class-string<AbstractDaftObject> $className
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
    * @psalm-return Generator<int, array{0:class-string<AbstractDaftObject>}, mixed, void>
    */
    final public function DataProviderNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues(
    ) : Generator {
        foreach ($this->dataProviderImplementations() as $args) {
            if (
                is_a($args[0], AbstractDaftObject::class, true) &&
                ! is_a(
                    $args[0],
                    DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class,
                    true
                )
            ) {
                yield [$args[0]];
            }
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
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:array<string, scalar|array|object|null>, 3:array<int, string>, 4:array<int, string>, 5:string}, mixed, void>
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
    * @dataProvider dataProviderNonAbstractGoodSortableImplementations
    *
    * @psalm-param class-string<DaftSortableObject> $className
    */
    public function testSortableImplementation(string $className) : void
    {
        $publicOrProtected = $className::DaftObjectPublicOrProtectedGetters();

        $properties = $className::DaftSortableObjectProperties();

        static::assertGreaterThan(
            0,
            count($properties),
            ($className . ' must specify one or more sortable properties!')
        );

        foreach ($properties as $v) {
            $expectedMethod = TypeUtilities::MethodNameFromProperty($v, false);

            static::assertTrue(in_array($v, $publicOrProtected, true));
            static::assertTrue(method_exists($className, $expectedMethod));
        }
    }

    /**
    * @dataProvider DataProviderNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues
    *
    * @psalm-param class-string<AbstractDaftObject> $implementation
    */
    public function testNotDaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues(
        string $implementation
    ) : void {
        static::expectException(ClassDoesNotImplementClassException::class);
        static::expectExceptionMessage(
            $implementation .
            ' does not implement ' .
            DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class
        );

        $implementation::DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues();
    }

    public function test_public_NudgePropertyValue() : void
    {
        $obj = new HasIdPublicNudge();

        static::expectException(UndefinedPropertyException::class);
        static::expectExceptionMessage(
            'Property not defined: ' .
            HasIdPublicNudge::class .
            '::$foo'
        );

        $obj->public_NudgePropertyValue('foo', 'bar');
    }

    /**
    * @dataProvider dataProvider_DaftObject__has_properties_each_defined_property
    *
    * @psalm-param class-string<AbstractDaftObject> $className
    */
    public function test_AbstractDaftObject__has_properties_each_property(
        string $className,
        string $property,
        bool $maybe_mixed_case = false
    ) : void {
        $reflection = new ReflectionClass($className);

        $docblock = $reflection->getDocComment();

        $getter_name = TypeUtilities::MethodNameFromProperty($property, false);
        $setter_name = TypeUtilities::MethodNameFromProperty($property, true);
        $has_getter = $reflection->hasMethod($getter_name);
        $has_setter = $reflection->hasMethod($setter_name);

        $getter = $has_getter ? $reflection->getMethod($getter_name) : null;
        $setter = $has_setter ? $reflection->getMethod($setter_name) : null;

        if (($getter instanceof ReflectionMethod) && ! ($setter instanceof ReflectionMethod)) {
            $docblock = $getter->getDeclaringClass()->getDocComment();
        } elseif (
            ! ($getter instanceof ReflectionMethod) &&
            ($setter instanceof ReflectionMethod)
        ) {
            $docblock = $setter->getDeclaringClass()->getDocComment();
        }

        static::assertIsString($docblock, $className . ' must implement a docblock!');

        $setter_param =
            (
                ($setter instanceof ReflectionMethod) &&
                (1 === $setter->getNumberOfRequiredParameters())
            )
                ? $setter->getParameters()[0]
                : null;
        $getter_type =
            (($getter instanceof ReflectionMethod) && $getter->hasReturnType())
                ? $getter->getReturnType()
                : null;

        $read_write_regex = '/\* @property(?:-(?:read|write))? ([^\$]+) \$' . preg_quote($property, '/') . '[\r\n]/';
        $read_regex = '/\* @property-read ([^\$]+) \$' . preg_quote($property, '/') . '[\r\n]/';
        $write_regex = '/\* @property-write ([^\$]+) \$' . preg_quote($property, '/') . '[\r\n]/';
        $matches = [];

        if ($maybe_mixed_case) {
            $read_write_regex .= 'i';
            $read_regex .= 'i';
            $write_regex .= 'i';
        }

        if (($getter instanceof ReflectionMethod) && ($setter instanceof ReflectionMethod)) {
            static::assertInstanceOf(ReflectionParameter::class, $setter_param);

            $getter_docblock = $getter->getDocComment();
            $setter_docblock = $getter->getDocComment();

            $skip = false;

            if (is_string($getter_docblock) && is_string($setter_docblock)) {
                $regex_setter_type =
                    '/\* @(psalm-param|param) (.+) $' .
                    preg_quote($setter_param->getName(), '/') .
                    '[\r\n]/';

                if (
                    (
                        1 === preg_match(
                            self::REGEX_PSALM_GETTER_TYPE,
                            $getter_docblock,
                            $getter_matches
                        ) ||
                        1 === preg_match(
                            self::REGEX_GETTER_TYPE,
                            $getter_docblock,
                            $getter_matches
                        )
                    ) &&
                    1 === preg_match($regex_setter_type, $setter_docblock, $setter_matches)
                ) {
                    static::assertSame(
                        $getter_matches[1],
                        $setter_matches[2],
                        (
                            'Return type of ' .
                            $getter->getDeclaringClass()->getName() .
                            '::' .
                            $getter->getName() .
                            '() must match param type of ' .
                            $setter->getDeclaringClass()->getName() .
                            '::' .
                            $setter->getName() .
                            '()'
                        )
                    );

                    static::assertRegExp(
                        $read_regex,
                        $docblock,
                        (
                            $className .
                            ' must specify both @property-read & @property-write docblock' .
                            ' entries for $' .
                            $property .
                            ', @property-read not specified!'
                        )
                    );

                    static::assertRegExp(
                        $write_regex,
                        $docblock,
                        (
                            $className .
                            ' must specify both @property-read & @property-write docblock' .
                            ' entries for $' .
                            $property .
                            ', @property-write not specified!'
                        )
                    );

                    $skip = true;
                }
            }

            if ( ! $skip) {
                static::assertSame(
                    1,
                    preg_match($read_write_regex, $docblock, $matches),
                    (
                        $className .
                        ' must specify an @property docblock entry or' .
                        ' both @property-read & @property-write docblocks entries for $' .
                        $property
                    )
                );
            }
        } elseif ($getter instanceof ReflectionMethod) {
            static::assertSame(
                1,
                preg_match($read_regex, $docblock, $matches),
                (
                    $getter->getDeclaringClass()->getName() .
                    ' must specify an @property-read docblock entry for ' .
                    $getter->getDeclaringClass()->getName() .
                    '::$' .
                    $property .
                    (
                        $maybe_mixed_case
                            ? (
                                ' or ' .
                                $getter->getDeclaringClass()->getName() .
                                '::$' .
                                lcfirst($property)
                            )
                            : ''
                    ) .
                    ' (via ' .
                    $getter->getDeclaringClass()->getName() .
                    '::' .
                    $getter->getName() .
                    '())'
                )
            );
        } elseif ($setter instanceof ReflectionMethod) {
            static::assertSame(
                1,
                preg_match($write_regex, $docblock, $matches),
                (
                    $setter->getDeclaringClass()->getName() .
                    ' must specify an @property-write docblock entry for ' .
                    $setter->getDeclaringClass()->getName() .
                    '::$' .
                    $property
                )
            );
        } else {
            static::assertTrue($reflection->isAbstract());
        }

        if ($setter_param instanceof ReflectionParameter) {
            $setter_type = $setter_param->hasType() ? $setter_param->getType() : null;

            if (
                $setter_type instanceof ReflectionNamedType &&
                'array' !== $setter_type->getName()
            ) {
                if ('|null' === mb_substr($matches[1], -5)) {
                    static::assertTrue($setter_type->allowsNull());
                } else {
                    static::assertSame($matches[1], $setter_type->getName());
                }
            } else {
                static::assertInstanceOf(ReflectionMethod::class, $setter);

                $setter_docblock = $setter->getDocComment();

                static::assertIsString(
                    $setter_docblock,
                    (
                        $setter->getDeclaringClass()->getName() .
                        '::' .
                        $setter->getName() .
                        '() must specifiy a docblock!'
                    )
                );

                $regex_setter_type =
                    '/\* @param (.+) \$' .
                    preg_quote($setter_param->getName(), '/') .
                    '\n/';

                static::assertSame(
                    1,
                    preg_match($regex_setter_type, $setter_docblock, $setter_matches),
                    (
                        $className .
                        ' must specify an @param docblock entry for ' .
                        $className .
                        '::' .
                        $setter->getName() .
                        '()'
                    )
                );
            }
        }

        if ($getter_type instanceof ReflectionNamedType && 'array' !== $getter_type->getName()) {
            if ('|null' === mb_substr($matches[1], -5)) {
                static::assertTrue($getter_type->allowsNull());
            }
        } elseif ($getter instanceof ReflectionMethod) {
            $getter_docblock = $getter->getDocComment();

            static::assertIsString(
                $getter_docblock,
                (
                    $getter->getDeclaringClass()->getName() .
                    '::' .
                    $getter->getName() .
                    '() must specify either an @return or @psalm-return docblock!'
                )
            );
            static::assertSame(
                1,
                (
                    preg_match(
                        self::REGEX_PSALM_GETTER_TYPE,
                        $getter_docblock,
                        $getter_matches
                    ) ?: preg_match(self::REGEX_GETTER_TYPE, $getter_docblock, $getter_matches)
                ),
                (
                    $className .
                    ' must specify an @return docblock entry for ' .
                    $className .
                    '::' .
                    $getter->getName() .
                    '()'
                )
            );

            if (
                'id' !== $property ||
                is_a($className, DefinesOwnIntegerIdInterface::class, true) ||
                is_a($className, DefinesOwnStringIdInterface::class, true)
            ) {
                static::assertSame($matches[1], $getter_matches[2]);
            }
        }
    }

    /**
    * @dataProvider dataProvider_NonAbstract_AbstractDaftObject__and__DaftObjectCreatedByArray__has_properties_each_defined_property__writeOnly
    *
    * @psalm-param class-string<AbstractDaftObject&DaftObjectCreatedByArray> $className
    */
    final public function test_NonAbstract_AbstractDaftObject__and__DaftObjectCreatedByArray__has_properties_each_defined_property__writeOnly(
        string $className,
        string $property
    ) : void {
        /**
        * @var AbstractDaftObject&DaftObjectCreatedByArray
        */
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

    public function testWriteOnly() : void
    {
        $obj = new WriteOnly();

        $obj->SetFoo('bar');
        $obj->SetBar(1.2);
        $obj->SetBaz(3);
        $obj->SetBat(true);
        $obj->SetBat(false);
        $obj->SetBat(null);

        static::assertSame('bar', $obj->GetId());

        $obj->SetFoo('baz');
        static::assertSame('baz', $obj->GetId());
    }

    /**
    * @psalm-suppress ForbiddenCode
    */
    final protected function VarDumpDaftObject(DaftObject $obj) : string
    {
        ob_start();
        var_dump($obj);

        return (string) ob_get_clean();
    }

    /**
    * @psalm-param T $obj
    */
    protected static function RegexForObject(DaftObject $obj) : string
    {
        /**
        * @var array<string, scalar|array|DaftObject|null>
        */
        $props = [];

        /**
        * @var array<int, string>
        */
        $exportables = $obj::DaftObjectExportableProperties();

        foreach ($exportables as $prop) {
            $expectedMethod = TypeUtilities::MethodNameFromProperty($prop, false);
            if (
                $obj->__isset($prop) &&
                method_exists($obj, $expectedMethod) &&
                (new ReflectionMethod($obj, $expectedMethod))->isPublic()
            ) {
                $props[$prop] = $obj->__get($prop);
            }
        }

        return static::RegexForArray(get_class($obj), $props);
    }

    /**
    * @param array<string, scalar|object|array|null> $props
    */
    protected static function RegexForArray(string $className, array $props) : string
    {
        $regex =
            '(?:class |object\()' .
            preg_quote($className, '/') .
            '[\)]{0,1}#' .
            '\d+ \(' .
            preg_quote((string) count($props), '/') .
            '\) \{.+';

        foreach ($props as $prop => $val) {
            $regex .=
                ' (?:public ' .
                preg_quote('$' . $prop, '/') .
                '|' .
                preg_quote('["' . $prop . '"]', '/') .
                ')[ ]{0,1}' .
                preg_quote('=', '/') .
                '>.+' .
                static::RegexForVal($val) .
                '.+';
        }

        $regex .= '.+';

        return $regex;
    }

    /**
    * @param mixed $val
    */
    protected static function RegexForVal($val) : string
    {
        if (is_array($val)) {
            $out = '(?:';

            /**
            * @var (scalar|object|array|null)[]
            */
            $val = $val;

            foreach ($val as $v) {
                $out .= static::RegexForVal($v);
            }

            $out .= ')';

            return $out;
        } elseif ($val instanceof DateTimeImmutable) {
            return
                '(?:class |object){0,1}' .
                '\({0,1}' .
                preg_quote(DateTimeImmutable::class, '/') .
                '(?:\:\:__set_state\(array\(|\)?#\d+ \(\d+\) \{)' .
                '\s+(?:\["|\'|public \$)date(?:"\]|\'){0,1}\s*=>\s+(?:\'[^\']+\',|string\(\d+\) \"[^"]+\")' .
                '\s+(?:\["|\'|public \$)timezone_type(?:"\]|\'){0,1}\s*=>\s+(?:int\(){0,1}\d+(?:\)){0,1},{0,1}' .
                '\s+(?:\["|\'|public \$)timezone(?:"\]|\'){0,1}\s*=>\s+(?:\'[^\']+\',|string\(\d+\) \"[^"]+\")' .
                '\s*(?:\)\)|\})';
        }

        return
            (
                is_int($val)
                    ? 'int'
                    : (
                        is_bool($val)
                            ? 'bool'
                            : (
                                is_float($val)
                                    ? '(?:float|double)'
                                    : (is_object($val) ? '' : preg_quote(gettype($val), '/'))
                            )
                    )
            ) .
            (
                ($val instanceof DaftObject)
                    ? ('(?:' . static::RegexForObject($val) . ')')
                    : preg_quote(
                        (
                            '(' .
                            (
                                is_string($val)
                                    ? mb_strlen($val, '8bit')
                                    : (is_numeric($val) ? (string) $val : var_export($val, true))
                            ) .
                            ')' .
                            (is_string($val) ? (' "' . $val . '"') : '')
                        ),
                        '/'
                    )
        );
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
