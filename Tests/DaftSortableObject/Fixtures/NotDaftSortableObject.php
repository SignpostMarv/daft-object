<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftSortableObject\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject as Base;
use SignpostMarv\DaftObject\TraitSortableDaftObject;
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @template T as DaftSortableObject
*
* @property int $intSortOrder
*/
class NotDaftSortableObject extends Base
{
    /**
    * @use TraitSortableDaftObject<T>
    */
    use TraitSortableDaftObject;

    const PROPERTIES = [
        'intSortOrder',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    public function GetIntSortOrder() : int
    {
        return TypeUtilities::ExpectRetrievedValueIsIntish(
            'intSortOrder',
            $this->RetrievePropertyValueFromData('intSortOrder'),
            static::class
        );
    }

    public function SetIntSortOrder(int $value) : void
    {
        $this->NudgePropertyValue('intSortOrder', $value);
    }
}
