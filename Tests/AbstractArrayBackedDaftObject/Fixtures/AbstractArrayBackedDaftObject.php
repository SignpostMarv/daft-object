<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\AbstractArrayBackedDaftObject\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject as Base;

class AbstractArrayBackedDaftObject extends Base
{
    const NULLABLE_PROPERTIES = [
        'allows_null',
    ];

    /**
    * @return scalar|array|object|null
    */
    public function public_RetrievePropertyValueFromData(string $property)
    {
        return $this->RetrievePropertyValueFromData($property);
    }

    /**
    * @param scalar|array|object|null $val
    */
    public function public_RetrievePropertyValueFromDataExpectStringOrNull(
        string $property,
        $val
    ) {
        return $this->RetrievePropertyValueFromDataExpectStringOrNull($property, $val);
    }

    /**
    * @param scalar|array|object|null $val
    */
    public function public_RetrievePropertyValueFromDataExpectArrayOrNull(
        string $property,
        $val
    ) {
        return $this->RetrievePropertyValueFromDataExpectArrayOrNull($property, $val);
    }

    /**
    * @param scalar|array|object|null $val
    */
    public function public_RetrievePropertyValueFromDataExpectIntishOrNull(
        string $property,
        $val
    ) {
        return $this->RetrievePropertyValueFromDataExpectIntishOrNull($property, $val);
    }

    /**
    * @param scalar|array|object|null $val
    */
    public function public_RetrievePropertyValueFromDataExpectFloatishOrNull(
        string $property,
        $val
    ) {
        return $this->RetrievePropertyValueFromDataExpectFloatishOrNull($property, $val);
    }

    /**
    * @param scalar|array|object|null $val
    */
    public function public_RetrievePropertyValueFromDataExpectBoolishOrNull(
        string $property,
        $val
    ) {
        return $this->RetrievePropertyValueFromDataExpectBoolishOrNull($property, $val);
    }

    /**
    * @param scalar|array|object|null $val
    */
    public function public_RetrievePropertyValueFromDataExpectBoolish(
        string $property,
        $val
    ) {
        return $this->RetrievePropertyValueFromDataExpectBoolish($property, $val);
    }
}
