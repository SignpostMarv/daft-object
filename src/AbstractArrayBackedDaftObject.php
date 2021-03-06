<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* Array-backed daft objects.
*/
abstract class AbstractArrayBackedDaftObject extends AbstractDaftObject implements DaftObjectCreatedByArray
{
    const BOOL_DEFAULT_WRITEALL = false;

    const BOOL_DEFAULT_AUTOTRIMSTRINGS = false;

    const BOOL_DEFAULT_THROWIFNOTUNIQUE = false;

    /**
    * data for this instance.
    *
    * @var array<string, scalar|array|object|null>
    */
    private $data = [];

    /**
    * List of changed properties.
    *
    * @var array<string, bool>
    */
    private $changedProperties = [];

    /**
    * List of changed properties, for write-once read-many.
    *
    * @var array<string, bool>
    */
    private $wormProperties = [];

    /**
    * @param array<string, scalar|array|object|null> $data
    */
    public function __construct(array $data = [], bool $writeAll = false)
    {
        if (true === $writeAll) {
            foreach ($data as $k => $v) {
                $this->__set($k, $v);
            }
        } else {
            $this->data = $data;
        }
    }

    public function __isset(string $property) : bool
    {
        return
            in_array(
                $property,
                static::DaftObjectProperties(),
                DefinitionAssistant::IN_ARRAY_STRICT_MODE
            ) &&
            isset($this->data, $this->data[$property]);
    }

    public function ChangedProperties() : array
    {
        return array_keys($this->changedProperties);
    }

    public function MakePropertiesUnchanged(string ...$properties) : void
    {
        foreach ($properties as $property) {
            unset($this->changedProperties[$property]);
        }
    }

    public function HasPropertyChanged(string $property) : bool
    {
        return $this->changedProperties[$property] ?? false;
    }

    public function jsonSerialize() : array
    {
        /**
        * @var array<int, string>
        */
        $properties = static::DaftObjectJsonPropertyNames();

        /**
        * @var array<string, string>
        */
        $properties = array_combine($properties, $properties);

        return array_filter(
            array_map(
                /**
                * @return scalar|array|object|null
                */
                function (string $property) {
                    return $this->DoGetSet($property, false);
                },
                $properties
            ),
            /**
            * @param scalar|array|object|null $maybe
            */
            function ($maybe) : bool {
                return ! is_null($maybe);
            }
        );
    }

    /**
    * @param array<int|string, scalar|(scalar|array|object|null)[]|object|null> $array
    */
    public static function DaftObjectFromJsonArray(
        array $array,
        bool $writeAll = self::BOOL_DEFAULT_WRITEALL
    ) : DaftJson {
        $type = JsonTypeUtilities::ThrowIfNotDaftJson(static::class);

        $array = JsonTypeUtilities::ThrowIfJsonDefNotValid($type, $array);

        /**
        * @var array<int, string>
        */
        $props = array_keys($array);

        /**
        * @var array<int, scalar|object|array|null>
        */
        $vals = array_map(
            JsonTypeUtilities::DaftJsonClosure(
                $array,
                $writeAll,
                $type
            ),
            $props
        );

        return new $type(array_combine($props, $vals), $writeAll);
    }

    public static function DaftObjectFromJsonString(string $string) : DaftJson
    {
        /**
        * @var scalar|array<int|string, scalar|(scalar|array|object|null)[]|object|null>|object|null
        */
        $decoded = json_decode($string, true);

        return JsonTypeUtilities::ThrowIfNotDaftJson(static::class)::DaftObjectFromJsonArray(
            is_array($decoded) ? $decoded : [$decoded]
        );
    }

    public function DaftObjectWormPropertyWritten(string $property) : bool
    {
        $wormProperties = $this->wormProperties;

        return
            ($this instanceof DaftObjectWorm) &&
            (
                $this->HasPropertyChanged($property) ||
                isset($wormProperties[$property])
            );
    }

    /**
    * Retrieve a property from data.
    *
    * @param string $property the property being retrieved
    *
    * @throws Exceptions\PropertyNotNullableException if value is not set and $property is not listed as nullabe
    *
    * @return scalar|array|object|null the property value
    */
    protected function RetrievePropertyValueFromData(string $property)
    {
        $isNullable = in_array(
            $property,
            static::DaftObjectNullableProperties(),
            DefinitionAssistant::IN_ARRAY_STRICT_MODE
        );

        if ( ! array_key_exists($property, $this->data) && ! $isNullable) {
            throw Exceptions\Factory::PropertyNotNullableException(static::class, $property);
        } elseif ($isNullable) {
            return $this->data[$property] ?? null;
        }

        return $this->data[$property];
    }

    protected function RetrievePropertyValueFromDataExpectStringOrNull(string $property) : ? string
    {
        $value = $this->RetrievePropertyValueFromData($property);

        if (is_null($value)) {
            return null;
        }

        return TypeUtilities::ExpectRetrievedValueIsString($property, $value, static::class);
    }

    protected function RetrievePropertyValueFromDataExpectArrayOrNull(string $property) : ? array
    {
        $value = $this->RetrievePropertyValueFromData($property);

        if (is_null($value)) {
            return null;
        }

        return TypeUtilities::ExpectRetrievedValueIsArray($property, $value, static::class);
    }

    protected function RetrievePropertyValueFromDataExpectIntishOrNull(string $property) : ? int
    {
        $value = $this->RetrievePropertyValueFromData($property);

        if (is_null($value)) {
            return null;
        }

        return TypeUtilities::ExpectRetrievedValueIsIntish($property, $value, static::class);
    }

    protected function RetrievePropertyValueFromDataExpectFloatishOrNull(string $property) : ? float
    {
        $value = $this->RetrievePropertyValueFromData($property);

        if (is_null($value)) {
            return null;
        }

        return TypeUtilities::ExpectRetrievedValueIsFloatish($property, $value, static::class);
    }

    protected function RetrievePropertyValueFromDataExpectBoolishOrNull(string $property) : ? bool
    {
        $value = $this->RetrievePropertyValueFromData($property);

        if (is_null($value)) {
            return null;
        }

        return TypeUtilities::ExpectRetrievedValueIsBoolish($property, $value, static::class);
    }

    /**
    * @param scalar|array|object|null $value
    */
    protected function NudgePropertyValue(
        string $property,
        $value,
        bool $autoTrimStrings = self::BOOL_DEFAULT_AUTOTRIMSTRINGS,
        bool $throwIfNotUnique = self::BOOL_DEFAULT_THROWIFNOTUNIQUE
    ) : void {
        TypeUtilities::MaybeThrowOnNudge(static::class, $property, $value);

        if ($this->DaftObjectWormPropertyWritten($property)) {
            throw Exceptions\Factory::PropertyNotRewriteableException(static::class, $property);
        }

        $value = $this->MaybeModifyValueBeforeNudge(
            $property,
            $value,
            $autoTrimStrings,
            $throwIfNotUnique
        );

        $isChanged = (
            ! array_key_exists($property, $this->data) ||
            $this->data[$property] !== $value
        );

        $this->data[$property] = $value;

        if ($isChanged && true !== isset($this->changedProperties[$property])) {
            $this->changedProperties[$property] = $this->wormProperties[$property] = true;
        }
    }

    /**
    * @param scalar|array|object|null $value
    *
    * @return scalar|array|object|null
    */
    private function MaybeModifyValueBeforeNudge(
        string $property,
        $value,
        bool $autoTrimStrings = self::BOOL_DEFAULT_AUTOTRIMSTRINGS,
        bool $throwIfNotUnique = self::BOOL_DEFAULT_THROWIFNOTUNIQUE
    ) {
        $spec = null;

        if (
            is_a(
                static::class,
                DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues::class,
                true
            )
        ) {
            $spec = (
                static::DaftObjectPropertiesWithMultiTypedArraysOfUniqueValues()[$property] ?? null
            );
        }

        if (is_array($spec)) {
            $value = DefinitionAssistant::MaybeThrowIfValueDoesNotMatchMultiTypedArray(
                $autoTrimStrings,
                $throwIfNotUnique,
                $value,
                ...$spec
            );
        }

        if (is_string($value) && $autoTrimStrings) {
            $value = trim($value);
        }

        return $value;
    }
}
