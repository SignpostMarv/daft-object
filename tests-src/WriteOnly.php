<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class WriteOnly extends AbstractTestObject implements DefinesOwnStringIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<WriteOnly>
    */
    use DaftObjectIdValuesHashLazyInt;

    public function SetFoo(string $value) : void
    {
        $this->NudgePropertyValue('Foo', $value);
    }

    public function SetBar(float $value) : void
    {
        $this->NudgePropertyValue('Bar', $value);
    }

    public function SetBaz(int $value) : void
    {
        $this->NudgePropertyValue('Baz', $value);
    }

    public function SetBat(? bool $value) : void
    {
        $this->NudgePropertyValue('Bat', $value);
    }

    public function GetId() : string
    {
        return (string) $this->RetrievePropertyValueFromData('Foo');
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['Foo'];
    }
}
