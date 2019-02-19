<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;
use InvalidArgumentException;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant as Base;

/**
* @template T as DaftObject
*
* @template-extends Base<T>
*/
class DefinitionAssistant extends Base
{
    const BOOL_EXPECTING_GETTER = false;

    const BOOL_EXPECTING_SETTER = true;

    const INT_ARRAY_INDEX_TYPE = 0;

    const INT_ARRAY_INDEX_GETTER = 1;

    const INT_ARRAY_INDEX_SETTER = 2;

    const IS_A_STRINGS = true;

    /**
    * @template A as AbstractDaftObject
    *
    * @psalm-param class-string<A> $maybe
    *
    * @psalm-return class-string<A&T>
    */
    public static function RegisterAbstractDaftObjectType(string $maybe) : string
    {
        /**
        * @var array<int, string>
        */
        $props = $maybe::PROPERTIES;

        /**
        * @psalm-var class-string<A>
        */
        $maybe = static::RegisterDaftObjectTypeFromTypeAndProps($maybe, ...$props);

        return $maybe;
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param class-string<T>|T $maybe
    */
    public static function ObtainExpectedProperties($maybe) : array
    {
        /**
        * @psalm-var class-string<T>
        */
        $maybe = is_string($maybe) ? $maybe : get_class($maybe);

        if (static::IsTypeUnregistered($maybe)) {
            if (is_a($maybe, AbstractDaftObject::class, true)) {
                /**
                * @psalm-var class-string<AbstractDaftObject&T>
                */
                $maybe = $maybe;

                /**
                * @psalm-var class-string<T>
                */
                $maybe = static::RegisterAbstractDaftObjectType($maybe);
            }
        }

        $maybe = self::MaybeRegisterAdditionalTypes($maybe);

        return parent::ObtainExpectedProperties($maybe);
    }

    /**
    * @psalm-return Closure(string):?string
    */
    public static function SetterOrGetterClosure(
        string $type,
        bool $SetNotGet,
        string ...$props
    ) : Closure {
        return function (string $property) use ($type, $props, $SetNotGet) : ? string {
            if (in_array($property, $props, self::IN_ARRAY_STRICT_MODE)) {
                /**
                * @var string
                */
                $method = TypeUtilities::MethodNameFromProperty($property, $SetNotGet);

                if (method_exists($type, $method)) {
                    return $method;
                }
            }

            return null;
        };
    }

    /**
    * @param scalar|array|object|null $value
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

        /**
        * @var (scalar|array|object|null)[]
        */
        $value = $value;

        return static::MaybeThrowIfValueDoesNotMatchMultiTypedArrayValueArray(
            $autoTrimStrings,
            $throwIfNotUnique,
            $value,
            ...$types
        );
    }

    /**
    * @psalm-param class-string<T> $maybe
    *
    * @psalm-return class-string<T>
    */
    protected static function RegisterDaftObjectTypeFromTypeAndProps(
        string $maybe,
        string $prop,
        string ...$props
    ) : string {
        array_unshift($props, $prop);

        $args = static::TypeAndGetterAndSetterClosureWithProps($maybe, ...$props);

        /**
        * @var array<int, string>
        */
        $props = array_slice($args, 3);

        static::RegisterType($args[self::INT_ARRAY_INDEX_TYPE], $args[self::INT_ARRAY_INDEX_GETTER], $args[self::INT_ARRAY_INDEX_SETTER], ...$props);

        /**
        * @psalm-var class-string<T>
        */
        $out = self::MaybeRegisterAdditionalTypes($args[self::INT_ARRAY_INDEX_TYPE]);

        return $out;
    }

    /**
    * @psalm-param class-string<T> $maybe
    *
    * @psalm-return class-string<T>
    */
    protected static function MaybeRegisterAdditionalTypes(string $maybe) : string
    {
        /**
        * @psalm-var class-string<T>
        */
        $out = array_reduce(
            array_filter(
                [
                    DefinesOwnIdPropertiesInterface::class,
                ],
                function (string $otherType) use ($maybe) : bool {
                    return $otherType !== $maybe;
                }
            ),
            /**
            * @psalm-param class-string<T> $maybe
            * @psalm-param class-string<T> $otherType
            *
            * @psalm-return class-string<T>
            */
            function (string $maybe, string $otherType) : string {
                if (self::IsTypeUnregistered($otherType)) {
                    return self::RegisterDaftObjectTypeFromTypeAndProps($otherType, 'id');
                }

                return $maybe;
            },
            $maybe
        );

        return $out;
    }

    /**
    * @psalm-param class-string<T> $type
    *
    * @psalm-return array{0:class-string<T>, 1:null|Closure(string):?string, 2:null|Closure(string):?string, 4:string}
    */
    private static function TypeAndGetterAndSetterClosureWithProps(
        string $type,
        string ...$props
    ) : array {
        /**
        * @psalm-var array{0:class-string<T>, 1:null|Closure(string):?string, 2:null|Closure(string):?string, 4:string}
        */
        $out = array_merge(
            [
                $type,
                static::SetterOrGetterClosure($type, self::BOOL_EXPECTING_GETTER, ...$props),
                static::SetterOrGetterClosure($type, self::BOOL_EXPECTING_SETTER, ...$props),
            ],
            $props
        );

        return $out;
    }

    /**
    * @param (scalar|array|object|null)[] $value
    *
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

        /**
        * @var (scalar|array|object|null)[]
        */
        $value = static::MaybeRemapStringsToTrimmedStrings($value, $autoTrimStrings, ...$types);

        $initialCount = count($value);

        /**
        * @var array<int, mixed>
        */
        $out = [];

        foreach ($value as $maybe) {
            if ( ! in_array($maybe, $out, true)) {
                $out[] = $maybe;
            }
        }

        if ($throwIfNotUnique && count($out) !== $initialCount) {
            throw new InvalidArgumentException(
                'Argument 3 passed to ' .
                __METHOD__ .
                ' contained non-unique values!'
            );
        }

        return $out;
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

                return in_array(
                    gettype($maybe),
                    $types,
                    DefinitionAssistant::IN_ARRAY_STRICT_MODE
                );
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
        if (
            $autoTrimStrings &&
            in_array('string', $types, DefinitionAssistant::IN_ARRAY_STRICT_MODE)
        ) {
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
}
