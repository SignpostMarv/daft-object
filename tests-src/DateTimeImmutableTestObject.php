<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use DateTimeImmutable;

class DateTimeImmutableTestObject extends AbstractArrayBackedDaftObject
{
    const STR_FORMAT_TEST = 'Y-m-d\TH:i:s.uP';

    const PROPERTIES = [
        'datetime',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    const JSON_PROPERTIES = self::EXPORTABLE_PROPERTIES;

    public function GetDatetime() : DateTimeImmutable
    {
        /**
        * @var DateTimeImmutable|string
        */
        $in = $this->RetrievePropertyValueFromData('datetime');

        if ($in instanceof DateTimeImmutable) {
            return (new DateTimeImmutable())->createFromFormat(
                self::STR_FORMAT_TEST,
                $in->format(self::STR_FORMAT_TEST)
            );
        }

        return new DateTimeImmutable($in);
    }

    public function SetDatetime(DateTimeImmutable $value)
    {
        $this->NudgePropertyValue('datetime', $value);
    }
}
