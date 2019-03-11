<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* Base daft object.
*/
interface DaftObject
{
    /**
    * Maps param $property to the getter method.
    *
    * @param string $property the property being retrieved
    *
    * @throws Exceptions\UndefinedPropertyException if a property is undefined
    *
    * @return scalar|array|object|null
    */
    public function __get(string $property);

    /**
    * Maps param $property to the getter method.
    *
    * @param string $property the property being retrieved
    * @param scalar|array|object|null $v
    *
    * @throws Exceptions\NotPublicSetterPropertyException if a property is not publicly settable
    */
    public function __set(string $property, $v) : void;

    /**
    * required to support isset($foo->bar);.
    *
    * @param string $property the property being checked
    */
    public function __isset(string $property) : bool;

    /**
    * required to support unset($foo->bar).
    *
    * @param string $property the property being unset
    */
    public function __unset(string $property) : void;

    /**
    * Get the changed properties on an object.
    *
    * @return string[]
    */
    public function ChangedProperties() : array;

    /**
    * Mark the specified properties as unchanged.
    *
    * @param string ...$properties the property being set as unchanged
    */
    public function MakePropertiesUnchanged(string ...$properties) : void;

    /**
    * Check if a property exists on an object.
    *
    * @param string $property the property being checked
    */
    public function HasPropertyChanged(string $property) : bool;

    /**
    * List of properties that can be defined on an implementation.
    *
    * @return array<int, string>
    */
    public static function DaftObjectProperties() : array;

    /**
    * List of nullable properties that can be defined on an implementation.
    *
    * @return array<int, string>
    */
    public static function DaftObjectNullableProperties() : array;

    /**
    * List of exportable properties that can be defined on an implementation.
    *
    * @return array<int, string>
    */
    public static function DaftObjectExportableProperties() : array;

    /**
    * List of public getter properties.
    *
    * @return array<int, string>
    */
    public static function DaftObjectPublicGetters() : array;

    /**
    * List of public getter properties.
    *
    * @return array<int, string>
    */
    public static function DaftObjectPublicOrProtectedGetters() : array;

    /**
    * List of public setter properties.
    */
    public static function DaftObjectPublicSetters() : array;

    /**
    * @return array<string, array<int, string>>
    */
    public static function DaftObjectPropertiesChangeOtherProperties() : array;
}
