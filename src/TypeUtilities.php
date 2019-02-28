<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

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

    const BOOL_DEFAULT_SET_NOT_GET = false;

    const BOOL_DOES_NOT_HAVE_METHOD = false;

    const SUPPORTED_INVALID_LEADING_CHARACTERS = [
        '@',
    ];

    const BOOL_METHOD_IS_NOT_STATIC = false;

    /**
    * @var array<string, array<string, bool>>
    *
    * @psalm-var array<class-string<DaftObject>, array<string, bool>>
    */
    private static $Getters = [];

    /**
    * @var array<string, array<int, string>>
    *
    * @psalm-var array<class-string<DaftObject>, array<int, string>>
    */
    private static $publicSetters = [];

    /**
    * @psalm-param class-string<DaftObject> $class
    *
    * @return array<int, string>
    */
    public static function DaftObjectPublicGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(array_filter(self::$Getters[$class]));
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    *
    * @return array<int, string>
    */
    public static function DaftObjectPublicOrProtectedGetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return array_keys(self::$Getters[$class]);
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    */
    public static function DaftObjectPublicSetters(string $class) : array
    {
        static::CachePublicGettersAndSetters($class);

        return self::$publicSetters[$class];
    }

    public static function MethodNameFromProperty(
        string $prop,
        bool $SetNotGet = self::BOOL_DEFAULT_SET_NOT_GET
    ) : string {
        if (
            in_array(
                mb_substr($prop, 0, 1),
                self::SUPPORTED_INVALID_LEADING_CHARACTERS,
                DefinitionAssistant::IN_ARRAY_STRICT_MODE
            )
        ) {
            return ($SetNotGet ? 'Alter' : 'Obtain') . ucfirst(mb_substr($prop, 1));
        }

        return ($SetNotGet ? 'Set' : 'Get') . ucfirst($prop);
    }

    public static function HasMethod(
        string $class,
        string $property,
        bool $SetNotGet,
        bool $pub = self::BOOL_DEFAULT_EXPECTING_NON_PUBLIC_METHOD
    ) : bool {
        $method = static::MethodNameFromProperty($property, $SetNotGet);

        try {
            $ref = new ReflectionMethod($class, $method);

            return
                ($pub ? $ref->isPublic() : $ref->isProtected()) &&
                self::BOOL_METHOD_IS_NOT_STATIC === $ref->isStatic();
        } catch (ReflectionException $e) {
            return self::BOOL_DOES_NOT_HAVE_METHOD;
        }
    }

    /**
    * @template T as string
    *
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObject> $class_name
    */
    public static function ExpectRetrievedValueIsString(
        string $property,
        $value,
        string $class_name
    ) : string {
        /**
        * @psalm-var T
        */
        $value = static::MaybeThrowIfNotType($property, $value, $class_name, 'string');

        return $value;
    }

    /**
    * @template T as array
    *
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObject> $class_name
    */
    public static function ExpectRetrievedValueIsArray(
        string $property,
        $value,
        string $class_name
    ) : array {
        /**
        * @psalm-var T
        */
        $value = static::MaybeThrowIfNotType($property, $value, $class_name, 'array');

        return $value;
    }

    /**
    * @template T as int
    *
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObject> $class_name
    */
    public static function ExpectRetrievedValueIsIntish(
        string $property,
        $value,
        string $class_name
    ) : int {
        if (is_string($value) && ctype_digit($value)) {
            $value = (int) $value;
        }

        /**
        * @psalm-var T
        */
        $value = static::MaybeThrowIfNotType($property, $value, $class_name, 'integer');

        return $value;
    }

    /**
    * @template T as float
    *
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObject> $class_name
    */
    public static function ExpectRetrievedValueIsFloatish(
        string $property,
        $value,
        string $class_name
    ) : float {
        if (is_string($value) && is_numeric($value)) {
            $value = (float) $value;
        }

        /**
        * @psalm-var T
        */
        $value = static::MaybeThrowIfNotType($property, $value, $class_name, 'double');

        return $value;
    }

    /**
    * @template T as bool
    *
    * @param scalar|array|object|null $value
    *
    * @psalm-param class-string<DaftObject> $class_name
    */
    public static function ExpectRetrievedValueIsBoolish(
        string $property,
        $value,
        string $class_name
    ) : bool {
        if ('1' === $value || 1 === $value) {
            return true;
        } elseif ('0' === $value || 0 === $value) {
            return false;
        }

        /**
        * @psalm-var T
        */
        $value = static::MaybeThrowIfNotType($property, $value, $class_name, 'boolean');

        return $value;
    }

    /**
    * @psalm-param class-string<DaftObject> $class_name
    *
    * @param scalar|array|object|null $value
    */
    public static function MaybeThrowOnNudge(
        string $class_name,
        string $property,
        $value
    ) : void {
        if (
            ! in_array(
                $property,
                $class_name::DaftObjectProperties(),
                DefinitionAssistant::IN_ARRAY_STRICT_MODE
            )
        ) {
            throw Exceptions\Factory::UndefinedPropertyException($class_name, $property);
        } elseif (
            true === is_null($value) &&
            ! in_array(
                $property,
                $class_name::DaftObjectNullableProperties(),
                DefinitionAssistant::IN_ARRAY_STRICT_MODE
            )
        ) {
            throw Exceptions\Factory::PropertyNotNullableException($class_name, $property);
        }
    }

    /**
    * @template T
    *
    * @param scalar|array|object|null $value
    *
    * @psalm-return T
    *
    * @return mixed
    */
    protected static function MaybeThrowIfNotType(
        string $property,
        $value,
        string $class_name,
        string $type
    ) {
        if ($type !== gettype($value)) {
            throw Exceptions\Factory::PropertyValueNotOfExpectedTypeException(
                $class_name,
                $property,
                $type
            );
        }

        /**
        * @var T
        */
        $value = $value;

        return $value;
    }

    /**
    * @template T as DaftObject
    *
    * @psalm-param class-string<T> $class
    */
    protected static function CachePublicGettersAndSettersProperties(string $class) : void
    {
        if (
            is_a($class, AbstractDaftObject::class, true) &&
            DefinitionAssistant::IsTypeUnregistered($class)
        ) {
            /**
            * @psalm-var class-string<T>
            */
            $class = DefinitionAssistant::RegisterAbstractDaftObjectType($class);
        }

        foreach (
            DefinitionAssistant::ObtainExpectedProperties($class) as $prop
        ) {
            static::CachePublicGettersAndSettersProperty($class, $prop);
        }
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    */
    protected static function CachePublicGettersAndSettersProperty(
        string $class,
        string $prop
    ) : void {
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

        if (static::HasMethod($class, $prop, self::BOOL_METHOD_IS_PUBLIC)) {
            self::$publicSetters[$class][] = $prop;
        }
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    */
    private static function CachePublicGettersAndSetters(string $class) : void
    {
        if (false === isset(self::$Getters[$class])) {
            self::$Getters[$class] = [];
            self::$publicSetters[$class] = [];

            if (
                is_a(
                    $class,
                    DefinesOwnIdPropertiesInterface::class,
                    true
                )
            ) {
                self::$Getters[$class]['id'] = self::BOOL_METHOD_IS_PUBLIC;
            }

            /**
            * @psalm-var class-string<DaftObject>
            */
            $class = $class;

            static::CachePublicGettersAndSettersProperties($class);
        }
    }
}
