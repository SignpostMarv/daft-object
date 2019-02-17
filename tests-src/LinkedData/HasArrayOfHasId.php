<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\LinkedData;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftJson;

/**
* @template T as HasArrayOfHasId
*
* @template-implements DaftJson<T>
*/
class HasArrayOfHasId extends AbstractArrayBackedDaftObject implements DaftJson
{
    const PROPERTIES = [
        'json',
        'single',
    ];

    const NULLABLE_PROPERTIES = [
        'single',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    const JSON_PROPERTIES = [
        'json' => (HasId::class . '[]'),
        'single' => HasId::class,
    ];

    /**
    * @return array<int, HasId>
    */
    public function GetJson() : array
    {
        return (array) $this->RetrievePropertyValueFromData('json');
    }

    public function SetJson(array $vals) : void
    {
        $this->NudgePropertyValue('json', $vals);
    }

    public function GetSingle() : ? HasId
    {
        return $this->RetrievePropertyValueFromData('single');
    }

    public function SetSingle(? HasId $value) : void
    {
        $this->NudgePropertyValue('single', $value);
    }
}
