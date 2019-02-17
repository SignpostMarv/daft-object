<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use Generator;
use PHPUnit\Framework\TestCase as Base;
use ReflectionClass;
use ReflectionMethod;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftSortableObject;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\ReadOnlyBadDefinesOwnId;
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @template T as DaftObject
*/
abstract class TestCase extends Base
{
    const MIN_EXPECTED_ARRAY_COUNT = 2;
    /**
    * @var bool
    */
    protected $backupGlobals = false;

    /**
    * @var bool
    */
    protected $backupStaticAttributes = false;

    /**
    * @var bool
    */
    protected $runTestInSeparateProcess = false;

    public static function MethodNameFromProperty(string $prop, bool $SetNotGet = false) : string
    {
        return TypeUtilities::MethodNameFromProperty($prop, $SetNotGet);
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftObject>}, mixed, void>
    */
    public function dataProviderImplementations() : Generator
    {
        foreach (
            [
                '/src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/tests-src/*.php' => 'SignpostMarv\\DaftObject\\',
                '/tests-src/LinkedData/*.php' => 'SignpostMarv\\DaftObject\\LinkedData\\',
                '/Tests/DaftSortableObject/Fixtures/*.php' => 'SignpostMarv\\DaftObject\\Tests\\DaftSortableObject\\Fixtures\\',
            ] as $glob => $ns
        ) {
            $files = glob(__DIR__ . '/..' . $glob);
            foreach ($files as $file) {
                if (
                    is_file($file) &&
                    class_exists($className = ($ns . pathinfo($file, PATHINFO_FILENAME))) &&
                    is_a($className, DaftObject::class, true)
                ) {
                    yield [$className];
                }
            }
        }
    }

    /**
    * @return array<int, string>
    *
    * @psalm-return array<int, class-string<DaftObject>>
    */
    final public function dataProviderInvalidImplementations() : array
    {
        return [
            ReadOnlyBadDefinesOwnId::class,
        ];
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractImplementations() : Generator
    {
        foreach ($this->dataProviderImplementations() as $args) {
            if ( ! (($reflector = new ReflectionClass($args[0]))->isAbstract())) {
                yield [$args[0], $reflector];
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodImplementations() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach ($this->dataProviderNonAbstractImplementations() as $args) {
            if (false === in_array($args[0] ?? null, $invalid, true)) {
                $reflector = new ReflectionClass($args[0]);

                if ($reflector->isAbstract() || $reflector->isInterface()) {
                    static::markTestSkipped(
                        'Index 0 retrieved from ' .
                        get_class($this) .
                        '::dataProviderNonAbstractImplementations must be a' .
                        ' non-abstract, non-interface  implementation of ' .
                        DaftObject::class
                    );

                    return;
                }

                $properties = $args[0]::DaftObjectProperties();

                $initialCount = count($properties);

                if (
                    $initialCount !== count(
                        array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR)
                    )
                ) {
                    continue;
                }

                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass, 2:bool}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodImplementationsWithMixedCaseProperties() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach ($this->dataProviderNonAbstractImplementations() as $args) {
            if (false === in_array($args[0] ?? null, $invalid, true)) {
                list($implementation) = $args;

                $properties = $implementation::DaftObjectProperties();

                $initialCount = count($properties);

                if (
                    $initialCount === count(
                        array_unique(array_map('mb_strtolower', $properties), SORT_REGULAR)
                    )
                ) {
                    $args[] = false;
                } else {
                    $args[] = true;
                }

                /**
                * @psalm-var array{0:class-string<T>, 1:ReflectionClass, 2:bool}
                */
                $args = $args;

                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodImplementationsWithProperties() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (count($args[0]::DaftObjectProperties()) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&DefinesOwnIdPropertiesInterface>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractDefinesOwnIdGoodImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementationsWithProperties() as $args) {
            if (is_a($args[0], DefinesOwnIdPropertiesInterface::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodNullableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementationsWithProperties() as $args) {
            if (count($args[0]::DaftObjectNullableProperties()) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodExportableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (
                count($args[0]::DaftObjectExportableProperties()) > 0 &&
                count($args[0]::DaftObjectProperties()) > 0
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodPropertiesImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (count($args[0]::DaftObjectProperties()) > 0) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionMethod}, mixed, void>
    */
    final public function dataProviderNonAbstractGetterSetters() : Generator
    {
        foreach ($this->dataProviderNonAbstractImplementations() as $args) {
            foreach ($args[1]->getMethods() as $method) {
                if (preg_match('/^[GS]et[A-Z]/', $method->getName()) > 0) {
                    yield [$args[0], $method];
                }
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionMethod}, mixed, void>
    */
    final public function dataProviderGoodNonAbstractGetterSetters() : Generator
    {
        $invalid = $this->dataProviderInvalidImplementations();

        foreach ($this->dataProviderNonAbstractGetterSetters() as $args) {
            if (false === in_array($args[0], $invalid, true)) {
                yield [$args[0], $args[1]];
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T>, 1:ReflectionMethod}, mixed, void>
    */
    final public function dataProviderGoodNonAbstractGetterSettersNotId() : Generator
    {
        foreach ($this->dataProviderGoodNonAbstractGetterSetters() as $args) {
            $property = mb_substr($args[1]->getName(), 3);

            $properties = $args[0]::DaftObjectProperties();

            if (
                ! (
                    ! (
                        in_array($property, $properties, true) ||
                        in_array(lcfirst($property), $properties, true)
                    ) &&
                    is_a(
                        $args[0],
                        DefinesOwnIdPropertiesInterface::class,
                        true
                    )
                )
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&DaftSortableObject>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodSortableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (is_a($args[0], DaftSortableObject::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<T&AbstractDaftObject>, 1:ReflectionClass}, mixed, void>
    */
    final public function dataProviderNonAbstractGoodNonSortableImplementations() : Generator
    {
        foreach ($this->dataProviderNonAbstractGoodImplementations() as $args) {
            if (
                is_a($args[0], AbstractDaftObject::class, true) &&
                ! is_a($args[0], DaftSortableObject::class, true)
            ) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<AbstractDaftObject>}, mixed, void>
    */
    final public function dataProvider_AbstractDaftObject__is_subclass_of() : Generator
    {
        foreach ($this->dataProviderImplementations() as $args) {
            if (is_subclass_of($args[0], AbstractDaftObject::class, true)) {
                yield $args;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<AbstractDaftObject>}, mixed, void>
    */
    final public function dataProvider_AbstractDaftObject__has_properties() : Generator
    {
        foreach ($this->dataProvider_AbstractDaftObject__is_subclass_of() as $args) {
            if (count((array) $args[0]::PROPERTIES) > 0) {
                yield $args;
            }
        }
    }
}
