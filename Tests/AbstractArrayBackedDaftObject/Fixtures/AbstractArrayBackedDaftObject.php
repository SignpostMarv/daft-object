<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\AbstractArrayBackedDaftObject\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject as Base;

class AbstractArrayBackedDaftObject extends Base
{
    /**
    * @return scalar|array|object|null
    */
    public function public_RetrievePropertyValueFromData(string $property)
    {
        return $this->RetrievePropertyValueFromData($property);
    }
}
