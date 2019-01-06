<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

trait DaftObjectIdValuesHashLazyInt
{
    /**
    * @var array<string, array<string, string>>
    */
    private static $ids = [];

    /**
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdHash()
    */
    public static function DaftObjectIdHash(DefinesOwnIdPropertiesInterface $object) : string
    {
        $id = [];

        /**
        * @var array<int, string>
        */
        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            /**
            * @var scalar|array|object|resource|null
            */
            $val = $object->$prop;

            $id[] = (string) $val;
        }

        return static::DaftObjectIdValuesHash($id);
    }

    /**
    * @param string[] $id
    *
    * @see DefinesOwnIdPropertiesInterface::DaftObjectIdValuesHash()
    */
    public static function DaftObjectIdValuesHash(array $id) : string
    {
        $className = static::class;

        $objectIds = '';

        foreach (array_values($id) as $i => $idVal) {
            if ($i >= TypeParanoia::INDEX_FIRST_ARG) {
                $objectIds .= '::';
            }
            $objectIds .= (string) $idVal;
        }

        if ( ! isset(self::$ids[$className])) {
            self::$ids[$className] = [];
        }

        if ( ! isset(self::$ids[$className][$objectIds])) {
            self::$ids[$className][$objectIds] = (string) count(self::$ids[$className]);
        }

        return (string) self::$ids[$className][$objectIds];
    }
}
