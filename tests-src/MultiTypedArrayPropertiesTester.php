<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use DateTimeImmutable;

/**
* @property-read array<int, DateTimeImmutable> $dates
* @property-write scalar|array|object|null $dates
* @property-read array<int, DateTimeImmutable|string> $datesOrStrings
* @property-write scalar|array|object|null $datesOrStrings
* @property-read array<int, string> $trimmedStrings
* @property-write scalar|array|object|null $trimmedStrings
* @property-read string $trimmedString
* @property-write scalar|array|object|null $trimmedString
*/
class MultiTypedArrayPropertiesTester
    extends
        AbstractArrayBackedDaftObject
    implements
        DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues
{
    const PROPERTIES = [
        'dates',
        'datesOrStrings',
        'trimmedStrings',
        'trimmedString',
    ];

    const PROPERTIES_WITH_MULTI_TYPED_ARRAYS = [
        'dates' => [
            DateTimeImmutable::class,
        ],
        'datesOrStrings' => [
            'string',
            DateTimeImmutable::class,
        ],
        'trimmedStrings' => [
            'string',
        ],
    ];

    /**
    * @return array<int, DateTimeImmutable>
    */
    public function GetDates() : array
    {
        /**
        * @var array<int, DateTimeImmutable>
        */
        $out = TypeUtilities::ExpectRetrievedValueIsArray(
            'dates',
            $this->RetrievePropertyValueFromData('dates'),
            static::class
        );

        return $out;
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetDates($value) : void
    {
        $this->NudgePropertyValue('dates', $value, false, true);
    }

    /**
    * @return array<int, DateTimeImmutable|string>
    */
    public function GetDatesOrStrings() : array
    {
        /**
        * @var array<int, DateTimeImmutable|string>
        */
        $out = TypeUtilities::ExpectRetrievedValueIsArray(
            'datesOrStrings',
            $this->RetrievePropertyValueFromData('datesOrStrings'),
            static::class
        );

        return $out;
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetDatesOrStrings($value) : void
    {
        $this->NudgePropertyValue('datesOrStrings', $value, false, true);
    }

    /**
    * @return array<int, string>
    */
    public function GetTrimmedStrings() : array
    {
        /**
        * @var array<int, string>
        */
        $out = TypeUtilities::ExpectRetrievedValueIsArray(
            'trimmedStrings',
            $this->RetrievePropertyValueFromData('trimmedStrings'),
            static::class
        );

        return $out;
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetTrimmedStrings($value) : void
    {
        $this->NudgePropertyValue('trimmedStrings', $value, true);
    }

    public function GetTrimmedString() : string
    {
        return TypeUtilities::ExpectRetrievedValueIsString(
            'trimmedString',
            $this->RetrievePropertyValueFromData('trimmedString'),
            static::class
        );
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetTrimmedString($value) : void
    {
        $this->NudgePropertyValue('trimmedString', $value, true);
    }
}
