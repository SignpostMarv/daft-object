<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use Closure;

/**
* Base daft object.
*/
abstract class AbstractDaftObject implements DaftObject
{
    /**
    * List of properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const PROPERTIES = [];

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const NULLABLE_PROPERTIES = [];

    /**
    * List of exportable properties that can be defined on an implementation.
    *
    * @var array<int, string>
    */
    const EXPORTABLE_PROPERTIES = [];

    /**
    * import/export definition for DaftJson.
    *
    * @var array<int, string>
    */
    const JSON_PROPERTIES = [];

    /**
    * List of sortable properties for DaftSortableObject.
    *
    * @var array<int, string>
    */
    const SORTABLE_PROPERTIES = [];

    /**
    * @var array<string, array<int, string>>
    */
    const CHANGE_OTHER_PROPERTIES = [];

    /**
    * @var array<string, array<int, string>>
    */
    const PROPERTIES_WITH_MULTI_TYPED_ARRAYS = [];

    public function __get(string $property)
    {
        return $this->DoGetSet($property, false);
    }

    /**
    * @param scalar|array|object|null $v
    */
    public function __set(string $property, $v) : void
    {
        $this->DoGetSet($property, true, $v);
    }

    /**
    * @see static::NudgePropertyValue()
    */
    public function __unset(string $property) : void
    {
        $this->NudgePropertyValue($property, null);
    }

    public static function DaftObjectProperties() : array
    {
        /**
        * @var array<int, string>
        */
        $out = static::PROPERTIES;

        return $out;
    }

    public static function DaftObjectNullableProperties() : array
    {
        /**
        * @var array<int, string>
        */
        $out = static::NULLABLE_PROPERTIES;

        return $out;
    }

    public static function DaftObjectExportableProperties() : array
    {
        /**
        * @var array<int, string>
        */
        $out = static::EXPORTABLE_PROPERTIES;

        return $out;
    }

    public static function DaftObjectPublicGetters() : array
    {
        return TypeUtilities::DaftObjectPublicGetters(static::class);
    }

    public static function DaftObjectPublicOrProtectedGetters() : array
    {
        return TypeUtilities::DaftObjectPublicOrProtectedGetters(static::class);
    }

    public static function DaftObjectPublicSetters() : array
    {
        return TypeUtilities::DaftObjectPublicSetters(static::class);
    }

    /**
    * @return array<int|string, string>
    */
    public static function DaftObjectJsonProperties() : array
    {
        /**
        * @var array<int|string, string>
        */
        $out = JsonTypeUtilities::ThrowIfNotDaftJson(static::class)::JSON_PROPERTIES;

        return $out;
    }

    /**
    * @return array<int, string>
    */
    final public static function DaftObjectJsonPropertyNames() : array
    {
        /**
        * @var array<int, string>
        */
        $out = [];

        /**
        * @var array<int|string, string>
        */
        $jsonProperties = static::DaftObjectJsonProperties();

        foreach ($jsonProperties as $k => $prop) {
            if (is_string($k)) {
                $prop = $k;
            }

            $out[] = $prop;
        }

        return $out;
    }

    /**
    * @return array<string, array<int, string>>
    */
    public static function DaftObjectPropertiesChangeOtherProperties() : array
    {
        /**
        * @var array<string, array<int, string>>
        */
        $out = static::CHANGE_OTHER_PROPERTIES;

        return $out;
    }

    /**
    * @return array<string, array<int, string>>
    */
    public static function DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues() : array
    {
        if (
            ! is_a(
                static::class,
                DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class,
                true
            )
        ) {
            throw Exceptions\Factory::ClassDoesNotImplementClassException(
                static::class,
                DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class
            );
        }

        /**
        * @var array<string, array<int, string>>
        */
        $out = static::PROPERTIES_WITH_MULTI_TYPED_ARRAYS;

        return $out;
    }

    /**
    * Nudge the state of a given property, marking it as dirty.
    *
    * @param string $property property being nudged
    * @param scalar|array|object|null $value value to nudge property with
    *
    * @throws Exceptions\UndefinedPropertyException if $property is not in static::DaftObjectProperties()
    * @throws Exceptions\PropertyNotNullableException if $property is not in static::DaftObjectNullableProperties()
    * @throws Exceptions\PropertyNotRewriteableException if class is write-once read-many and $property was already changed
    */
    abstract protected function NudgePropertyValue(string $property, $value) : void;

    protected function MaybeThrowOnDoGetSet(string $property, bool $setter, array $props) : void
    {
        if ( ! in_array($property, $props, DefinitionAssistant::IN_ARRAY_STRICT_MODE)) {
            if (
                ! in_array(
                    $property,
                    static::DaftObjectProperties(),
                    DefinitionAssistant::IN_ARRAY_STRICT_MODE
                )
            ) {
                throw Exceptions\Factory::UndefinedPropertyException(static::class, $property);
            } elseif ($setter) {
                throw Exceptions\Factory::NotPublicSetterPropertyException(static::class, $property);
            }

            throw Exceptions\Factory::NotPublicGetterPropertyException(static::class, $property);
        }
    }

    /**
    * @param scalar|array|object|null $v
    *
    * @return scalar|array|object|null
    */
    protected function DoGetSet(string $property, bool $setter, $v = null)
    {
        $props = $setter ? static::DaftObjectPublicSetters() : static::DaftObjectPublicGetters();

        $this->MaybeThrowOnDoGetSet($property, $setter, $props);

        /**
        * @var callable
        */
        $callable = [$this, TypeUtilities::MethodNameFromProperty($property, $setter)];
        $closure = Closure::fromCallable($callable);

        /**
        * @var scalar|array|object|null
        */
        $out = $closure->__invoke($v);

        return $out;
    }
}
