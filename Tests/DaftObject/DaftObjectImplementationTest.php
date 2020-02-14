<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionType;
use ReflectionNamedType;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectCreatedByArray;
use SignpostMarv\DaftObject\DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues;
use SignpostMarv\DaftObject\DaftSortableObject;
use SignpostMarv\DaftObject\Exceptions\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\Exceptions\NotPublicGetterPropertyException;
use SignpostMarv\DaftObject\Exceptions\UndefinedPropertyException;
use SignpostMarv\DaftObject\LinkedData\HasIdPublicNudge;
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

                /**
                * @var ReflectionType
                */
                $returnType = $reflectorGetter->getReturnType();

                if ($returnType instanceof ReflectionNamedType) {
                static::assertTrue(
                    ('void' !== $returnType->getName()),
                    (
                        $reflectorGetter->getNumberOfParameters() .
                        $reflectorGetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorGetter->getName() .
                        '() must have a non-void return type.'
                    )
                );
                }

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

                if ($returnType instanceof ReflectionNamedType) {
                static::assertSame(
                    'void',
                    $returnType->getName(),
                    (
                        $reflectorSetter->getDeclaringClass()->getName() .
                        '::' .
                        $reflectorSetter->getName() .
                        '() must specify a void return type, "' .
                        $returnType->getName() .
                        '" found.'
                    )
                );
                }

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
    * @dataProvider dataProviderGoodNonAbstractGetterSetters
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

        if ( ! $defined) {
            foreach (TypeUtilities::SUPPORTED_INVALID_LEADING_CHARACTERS as $char) {
                $defined = (
                    in_array($char . $property, $properties, true) ||
                    in_array($char . lcfirst($property), $properties, true)
                );

                if ($defined) {
                    break;
                }
            }
        }

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

        static::assertIsString(
            $reflection->getDocComment(),
            (
                $className .
                ' must implement a docblock!'
            )
        );

        $first = mb_substr($property, 0, 1);

        if ( ! in_array($first, TypeUtilities::SUPPORTED_INVALID_LEADING_CHARACTERS, true)) {
            $docblock_getter = null;
            $docblock_setter = null;

            $getter_name = TypeUtilities::MethodNameFromProperty($property, false);
            $setter_name = TypeUtilities::MethodNameFromProperty($property, true);
            $has_getter = $reflection->hasMethod($getter_name);
            $has_setter = $reflection->hasMethod($setter_name);

            $getter = $has_getter ? $reflection->getMethod($getter_name) : null;
            $setter = $has_setter ? $reflection->getMethod($setter_name) : null;

            if ( ! is_null($getter)) {
                $docblock_getter = $getter->getDeclaringClass()->getDocComment();
            }

            if ( ! is_null($setter)) {
                $docblock_setter = $setter->getDeclaringClass()->getDocComment();
            }

            $read_regex =
                '/\* @property(?:-read)? ([^\$]+) \$' .
                preg_quote($property, '/') .
                '[\r\n]/';
            $write_regex =
                '/\* @property(?:-write)? ([^\$]+) \$' .
                preg_quote($property, '/') .
                '[\r\n]/';

            if ($maybe_mixed_case) {
                $read_regex .= 'i';
                $write_regex .= 'i';
            }

            if ( ! is_null($getter)) {
                static::assertIsString(
                    $docblock_getter,
                    (
                        $getter->getDeclaringClass()->name .
                        ' must implement a docblock!'
                    )
                );
                static::assertRegExp(
                    $read_regex,
                    $docblock_getter,
                    (
                        $className .
                        ' must specify an @property or @property-read docblocks entry for $' .
                        $property
                    )
                );
            }

            if ( ! is_null($setter)) {
                static::assertIsString(
                    $docblock_setter,
                    (
                        $setter->getDeclaringClass()->name .
                        ' must implement a docblock!'
                    )
                );
                static::assertRegExp(
                    $write_regex,
                    $docblock_setter,
                    (
                        $className .
                        ' must specify an @property or @property-write docblocks entry for $' .
                        $property
                    )
                );
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
}
