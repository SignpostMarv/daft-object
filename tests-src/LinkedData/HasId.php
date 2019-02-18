<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\LinkedData;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftJsonLinkedData;
use SignpostMarv\DaftObject\DefinesOwnStringIdInterface;
use SignpostMarv\DaftObject\DaftObjectIdValuesHashLazyInt;

/**
* @template T as HasId
*
* @template-implements DaftJsonLinkedData<T>
*
* @property string $@id
*/
class HasId extends AbstractArrayBackedDaftObject implements DaftJsonLinkedData, DefinesOwnStringIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<T>
    */
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = [
        '@id',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    const JSON_PROPERTIES = self::EXPORTABLE_PROPERTIES;

    public function AlterId(string $id) : void
    {
        $this->NudgePropertyValue('@id', trim($id));
    }

    public function ObtainId() : string
    {
        return (string) $this->RetrievePropertyValueFromData('@id');
    }

    public function GetId() : string
    {
        return $this->ObtainId();
    }

    public static function DaftObjectIdProperties() : array
    {
        return [
            '@id',
        ];
    }
}
