<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @property string $foo
*/
class NonPublicGetter extends AbstractArrayBackedDaftObject
{
    const PROPERTIES = [
        'foo',
    ];

    public function SetFoo(string $val) : void
    {
        $this->NudgePropertyValue('foo', $val);
    }

    protected function GetFoo() : string
    {
        return TypeUtilities::ExpectRetrievedValueIsString(
            'foo',
            $this->RetrievePropertyValueFromData('foo'),
            static::class
        );
    }
}
