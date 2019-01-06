<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;

class TypeUtilities
{
    const BOOL_EXPECTING_NON_PUBLIC_METHOD = false;

    const BOOL_EXPECTING_GETTER = false;

    const BOOL_DEFAULT_THROWIFNOTIMPLEMENTATION = false;

    const BOOL_DEFAULT_EXPECTING_NON_PUBLIC_METHOD = true;

    const BOOL_METHOD_IS_PUBLIC = true;

    const BOOL_METHOD_IS_NON_PUBLIC = false;

    const SUPPORTED_INVALID_LEADING_CHARACTERS = [
        '@',
    ];

    /**
    * @var array<string, array<string, bool>>
    */
    private static $Getters = [];

    /**
    * @var array<string, array<int, string>>
    */
    private static $publicSetters = [];

    public static function DaftObjectPublicGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(array_filter(self::$Getters[$class]));
    }

    public static function DaftObjectPublicOrProtectedGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(self::$Getters[$class]);
    }

    public static function DaftObjectPublicSetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return self::$publicSetters[$class];
    }

    private static function HasMethod(
        string $class,
        string $property,
        bool $SetNotGet,
        bool $pub = self::BOOL_DEFAULT_EXPECTING_NON_PUBLIC_METHOD
    ) : bool {
        $method = static::MethodNameFromProperty($property, $SetNotGet);

        try {
            $ref = new ReflectionMethod($class, $method);

            return ($pub ? $ref->isPublic() : $ref->isProtected()) && false === $ref->isStatic();
        } catch (ReflectionException $e) {
            return false;
        }
    }

    public static function MethodNameFromProperty(string $prop, bool $SetNotGet = false) : string
    {
        if (TypeParanoia::MaybeInArray(mb_substr($prop, 0, 1), self::SUPPORTED_INVALID_LEADING_CHARACTERS)) {
            return ($SetNotGet ? 'Alter' : 'Obtain') . ucfirst(mb_substr($prop, 1));
        }

        return ($SetNotGet ? 'Set' : 'Get') . ucfirst($prop);
    }

    /**
    * Checks if a type correctly defines it's own id.
    *
    * @throws ClassDoesNotImplementClassException if $class is not an implementation of DefinesOwnIdPropertiesInterface
    * @throws ClassMethodReturnHasZeroArrayCountException if $class::DaftObjectIdProperties() does not contain at least one property
    * @throws ClassMethodReturnIsNotArrayOfStringsException if $class::DaftObjectIdProperties() is not string[]
    * @throws UndefinedPropertyException if an id property is not in $class::DaftObjectIdProperties()
    */
    public static function CheckTypeDefinesOwnIdProperties(
        string $class,
        bool $throwIfNotImplementation = self::BOOL_DEFAULT_THROWIFNOTIMPLEMENTATION
    ) : void {
        if (TypeParanoia::IsThingStrings($class, DefinesOwnIdPropertiesInterface::class)) {
            self::CheckTypeDefinesOwnIdPropertiesIsImplementation($class);
        } elseif ($throwIfNotImplementation) {
            throw new ClassDoesNotImplementClassException(
                $class,
                DefinesOwnIdPropertiesInterface::class
            );
        }
    }

    /**
    * @param mixed $value
    *
    * @return array<int, mixed> filtered $value
    */
    public static function MaybeThrowIfValueDoesNotMatchMultiTypedArray(
        bool $autoTrimStrings,
        bool $throwIfNotUnique,
        $value,
        string ...$types
    ) : array {
        if ( ! is_array($value)) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' must be an array, ' .
                (is_object($value) ? get_class($value) : gettype($value)) .
                ' given!'
            );
        }

        return static::MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray(
            $autoTrimStrings,
            $throwIfNotUnique,
            $value,
            ...$types
        );
    }

    /**
    * @param mixed $maybe
    */
    private static function FilterMaybeArray($maybe, callable $filter) : array
    {
        return array_filter(
            TypeParanoia::EnsureArgumentIsArray($maybe, TypeParanoia::INDEX_FIRST_ARG, __METHOD__),
            $filter
        );
    }

    /**
    * @param mixed $maybe
    */
    private static function CountMaybeArray($maybe) : int
    {
        return count(TypeParanoia::EnsureArgumentIsArray(
            $maybe,
            TypeParanoia::INDEX_FIRST_ARG,
            __METHOD__
        ));
    }

    /**
    * @return array<int, mixed> filtered $value
    */
    private static function MaybeThrowIfNotArrayIntKeys(array $value) : array
    {
        $initialCount = count($value);

        /**
        * @var array<int, mixed>
        */
        $value = array_filter($value, 'is_int', ARRAY_FILTER_USE_KEY);

        if (count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' must be array<int, mixed>'
            );
        }

        return $value;
    }

    /**
    * @param array<int, mixed> $value
    *
    * @return array<int, mixed> filtered $value
    */
    private static function MaybeThrowIfValueArrayDoesNotMatchTypes(
        array $value,
        string ...$types
    ) : array {
        $initialCount = count($value);

        $value = array_filter(
            $value,
            /**
            * @param mixed $maybe
            */
            function ($maybe) use ($types) : bool {
                if (is_object($maybe)) {
                    foreach ($types as $maybeType) {
                        if (is_a($maybe, $maybeType)) {
                            return true;
                        }
                    }

                    return false;
                }

                return TypeParanoia::MaybeInArray(gettype($maybe), $types);
            }
        );

        if (count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' contained values that did not match the provided types!'
            );
        }

        return $value;
    }

    /**
    * @param array<int, mixed> $value
    *
    * @return array<int, mixed>
    */
    private static function MaybeRemapStringsToTrimmedStrings(
        array $value,
        bool $autoTrimStrings,
        string ...$types
    ) : array {
        if (TypeParanoia::MaybeInArray('string', $types) && $autoTrimStrings) {
            $value = array_map(
                /**
                * @param mixed $maybe
                *
                * @return mixed
                */
                function ($maybe) {
                    return is_string($maybe) ? trim($maybe) : $maybe;
                },
                $value
            );
        }

        return $value;
    }

    /**
    * @return array<int, mixed> filtered $value
    */
    private static function MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray(
        bool $autoTrimStrings,
        bool $throwIfNotUnique,
        array $value,
        string ...$types
    ) : array {
        $value = static::MaybeThrowIfNotArrayIntKeys($value);
        $value = static::MaybeThrowIfValueArrayDoesNotMatchTypes($value, ...$types);
        $value = static::MaybeRemapStringsToTrimmedStrings($value, $autoTrimStrings, ...$types);

        $initialCount = count($value);

        $value = array_unique($value, SORT_REGULAR);

        if ($throwIfNotUnique && count($value) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' contained non-unique values!'
            );
        }

        return array_values($value);
    }

    private static function CachePublicGettersAndSetters(string $class) : void
    {
        if (false === isset(self::$Getters[$class])) {
            self::$Getters[$class] = [];
            self::$publicSetters[$class] = [];

            if (TypeParanoia::IsThingStrings($class, DefinesOwnIdPropertiesInterface::class)) {
                self::$Getters[$class]['id'] = true;
            }

            self::CachePublicGettersAndSettersProperties($class);
        }
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    private static function CachePublicGettersAndSettersProperties(string $class) : void
    {
        /**
        * @var string[]
        */
        $props = $class::DaftObjectProperties();

        foreach ($props as $prop) {
            if (static::HasMethod($class, $prop, self::BOOL_EXPECTING_GETTER)) {
                self::$Getters[$class][$prop] = self::BOOL_METHOD_IS_PUBLIC;
            } elseif (static::HasMethod(
                $class,
                $prop,
                self::BOOL_EXPECTING_GETTER,
                self::BOOL_EXPECTING_NON_PUBLIC_METHOD
            )) {
                self::$Getters[$class][$prop] = self::BOOL_METHOD_IS_NON_PUBLIC;
            }

            if (static::HasMethod($class, $prop, true)) {
                self::$publicSetters[$class][] = $prop;
            }
        }
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    private static function CheckTypeDefinesOwnIdPropertiesIsImplementation(
        string $class
    ) : void {
        /**
        * @var scalar|array|object|null
        */
        $properties = $class::DaftObjectIdProperties();

        if (self::CountMaybeArray($properties) < 1) {
            throw new ClassMethodReturnHasZeroArrayCountException(
                $class,
                'DaftObjectIdProperties'
            );
        } elseif (
            self::CountMaybeArray($properties) !==
            count(self::FilterMaybeArray($properties, 'is_string'))
        ) {
            throw new ClassMethodReturnIsNotArrayOfStringsException(
                $class,
                'DaftObjectIdProperties'
            );
        }
    }
}
