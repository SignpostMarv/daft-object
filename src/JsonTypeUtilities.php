<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;

class JsonTypeUtilities
{
    const IS_A_STRINGS = true;

    const INT_TYPE_EXPECT_IS_ARRAY = -2;

    /**
    * @psalm-param class-string<DaftObject> $class
    *
    * @psalm-return class-string<DaftJson>
    */
    public static function ThrowIfNotDaftJson(string $class) : string
    {
        if ( ! is_a($class, DaftJson::class, self::IS_A_STRINGS)) {
            throw new DaftObjectNotDaftJsonBadMethodCallException($class);
        }

        return $class;
    }

    /**
    * @template T as DaftJson
    *
    * @psalm-param class-string<T> $jsonType
    *
    * @return array<int, DaftJson>|DaftJson
    *
    * @psalm-return array<int, T>|T
    */
    final public static function DaftJsonFromJsonType(
        string $jsonType,
        array $propVal,
        bool $writeAll
    ) {
        return JsonTypeUtilities::ArrayToJsonType($jsonType, $propVal, $writeAll);
    }

    /**
    * @param array<int|string, mixed> $array
    *
    * @psalm-param class-string<DaftJson> $type
    */
    public static function ThrowIfJsonDefNotValid(string $type, array $array) : array
    {
        $jsonProps = $type::DaftObjectJsonPropertyNames();
        $array = JsonTypeUtilities::FilterThrowIfJsonDefNotValid($type, $jsonProps, $array);
        $jsonDef = $type::DaftObjectJsonProperties();

        $keys = array_keys($array);

        /**
        * @var array<int|string, mixed>
        */
        $out = array_combine($keys, array_map(
            JsonTypeUtilities::MakeMapperThrowIfJsonDefNotValid($type, $jsonDef, $array),
            $keys
        ));

        return $out;
    }

    /**
    * @template T as DaftJson
    *
    * @psalm-return class-string<T>
    */
    public static function ThrowIfNotJsonType(string $jsonType) : string
    {
        if ( ! is_a($jsonType, DaftJson::class, DefinitionAssistant::IS_A_STRINGS)) {
            throw new ClassDoesNotImplementClassException($jsonType, DaftJson::class);
        }

        return $jsonType;
    }

    /**
    * @template T as DaftJson
    *
    * @param mixed[] $propVal
    *
    * @psalm-param class-string<T> $jsonType
    *
    * @return array<int, DaftJson>
    *
    * @psalm-return array<int, T>
    */
    public static function DaftObjectFromJsonTypeArray(
        string $jsonType,
        string $prop,
        array $propVal,
        bool $writeAll
    ) : array {
        return array_map(
            /**
            * @param mixed $val
            *
            * @psalm-return T
            *
            * @throws PropertyNotJsonDecodableShouldBeArrayException if $val is not an array
            */
            function ($val) use ($jsonType, $writeAll, $prop) : DaftJson {
                if ( ! is_array($val)) {
                    throw new PropertyNotJsonDecodableShouldBeArrayException($jsonType, $prop);
                }

                return JsonTypeUtilities::ArrayToJsonType($jsonType, $val, $writeAll);
            },
            array_values($propVal)
        );
    }

    /**
    * @param array<string|int, string> $jsonDef
    */
    private static function MakeMapperThrowIfJsonDefNotValid(
        string $class,
        array $jsonDef,
        array $array
    ) : Closure {
        $mapper =
            /**
            * @return mixed
            */
            function (string $prop) use ($jsonDef, $array, $class) {
                if (isset($jsonDef[$prop]) && false === is_array($array[$prop])) {
                    static::ThrowBecauseArrayJsonTypeNotValid(
                        $class,
                        $jsonDef[$prop],
                        $prop
                    );
                }

                return $array[$prop];
            };

        return $mapper;
    }

    /**
    * @psalm-param class-string<DaftJson> $class
    */
    private static function FilterThrowIfJsonDefNotValid(
        string $class,
        array $jsonProps,
        array $array
    ) : array {
        $filter = function (string $prop) use ($jsonProps, $array, $class) : bool {
            if ( ! in_array($prop, $jsonProps, DefinitionAssistant::IN_ARRAY_STRICT_MODE)) {
                throw new PropertyNotJsonDecodableException($class, $prop);
            }

            return false === is_null($array[$prop]);
        };

        return array_filter($array, $filter, ARRAY_FILTER_USE_KEY);
    }

    /**
    * @template T as DaftJson
    *
    * @psalm-param class-string<T> $type
    *
    * @psalm-return T
    */
    private static function ArrayToJsonType(string $type, array $value, bool $writeAll) : DaftJson
    {
        return $type::DaftObjectFromJsonArray($value, $writeAll);
    }

    private static function ThrowBecauseArrayJsonTypeNotValid(
        string $class,
        string $type,
        string $prop
    ) : void {
        if ('[]' === mb_substr($type, self::INT_TYPE_EXPECT_IS_ARRAY)) {
            throw new PropertyNotJsonDecodableShouldBeArrayException($class, $prop);
        }
        throw new PropertyNotJsonDecodableShouldBeArrayException($type, $prop);
    }
}
