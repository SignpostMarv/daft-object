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
* @template TSub as HasId
*
* @template-implements DaftJson<T>
*
* @property array<int, HasId> $json
* @property HasId|null $single
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
        /**
        * @psalm-var array<int, TSub>
        */
        $out = array_values(array_filter(
            (array) $this->RetrievePropertyValueFromData('json'),
            /**
            * @param scalar|array|object|null $maybe
            */
            function ($maybe) : bool {
                return $maybe instanceof HasId;
            }
        ));

        return $out;
    }

    /**
    * @param array<int, HasId> $vals
    */
    public function SetJson(array $vals) : void
    {
        $this->NudgePropertyValue('json', $vals);
    }

    public function GetSingle() : ? HasId
    {
        /**
        * @psalm-var TSub|null
        */
        $out = $this->RetrievePropertyValueFromData('single');

        return $out;
    }

    public function SetSingle(? HasId $value) : void
    {
        $this->NudgePropertyValue('single', $value);
    }
}
