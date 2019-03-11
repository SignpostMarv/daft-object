<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

trait DebugInfoTrait
{
    public function __debugInfo() : array
    {
        $getters = static::DaftObjectPublicGetters();
        $exportables = static::DaftObjectExportableProperties();

        /**
        * @var array<int, string>
        */
        $properties = array_filter($exportables, function (string $prop) use ($getters) : bool {
            return
                $this->__isset($prop) &&
                in_array($prop, $getters, DefinitionAssistant::IN_ARRAY_STRICT_MODE);
        });

        /**
        * @var array<string, scalar|array|object|null>
        */
        $out = array_combine($properties, array_map(
            /**
            * @return scalar|array|object|null
            */
            function (string $prop) {
                return $this->__get($prop);
            },
            $properties
        ));

        return $out;
    }

    /**
    * List of exportable properties that can be defined on an implementation.
    *
    * @return array<int, string>
    */
    abstract public static function DaftObjectExportableProperties() : array;

    /**
    * List of public getter properties.
    *
    * @return array<int, string>
    */
    abstract public static function DaftObjectPublicGetters() : array;
}
