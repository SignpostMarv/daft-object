<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftSortableObject\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject as Base;
use SignpostMarv\DaftObject\DaftSortableObject as Target;
use SignpostMarv\DaftObject\TraitSortableDaftObject;
use SignpostMarv\DaftObject\TypeUtilities;

/**
* @template T as DaftSortableObject
*
* @template-implements Target<T>
*
* @property int $intSortOrder
*/
class DaftSortableObject extends Base implements Target
{
    /**
    * @use TraitSortableDaftObject<T>
    */
    use TraitSortableDaftObject;

    const PROPERTIES = [
        'intSortOrder',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    const SORTABLE_PROPERTIES = [
        'intSortOrder',
    ];

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
