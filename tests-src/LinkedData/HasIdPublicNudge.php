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

/**
* @template T as HasId
*
* @template-implements DaftJsonLinkedData<T>
*/
class HasIdPublicNudge extends AbstractArrayBackedDaftObject implements DaftJsonLinkedData
{
    /**
    * @param scalar|array|object|null $value
    */
    public function public_NudgePropertyValue(
        string $property,
        $value,
        bool $autoTrimStrings = self::BOOL_DEFAULT_AUTOTRIMSTRINGS,
        bool $throwIfNotUnique = self::BOOL_DEFAULT_THROWIFNOTUNIQUE
    ) : void {
        $this->NudgePropertyValue($property, $value, $autoTrimStrings, $throwIfNotUnique);
    }
}
