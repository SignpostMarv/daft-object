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

    public function public_RetrievePropertyValueFromDataExpectStringOrNull(
        string $property
    ) : ? string {
        return $this->RetrievePropertyValueFromDataExpectStringOrNull($property);
    }

    public function public_RetrievePropertyValueFromDataExpectArrayOrNull(
        string $property
    ) : ? array {
        return $this->RetrievePropertyValueFromDataExpectArrayOrNull($property);
    }

    public function public_RetrievePropertyValueFromDataExpectIntishOrNull(
        string $property
    ) : ? int {
        return $this->RetrievePropertyValueFromDataExpectIntishOrNull($property);
    }

    public function public_RetrievePropertyValueFromDataExpectFloatishOrNull(
        string $property
    ) : ? float {
        return $this->RetrievePropertyValueFromDataExpectFloatishOrNull($property);
    }

    public function public_RetrievePropertyValueFromDataExpectBoolishOrNull(
        string $property
    ) : ? bool {
        return $this->RetrievePropertyValueFromDataExpectBoolishOrNull($property);
    }
}
