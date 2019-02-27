<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @property-write string $Foo
* @property-write float $Bar
* @property-write bool|null $Bat
* @property-write int $Baz
* @property-read string $id
*/
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
        return $this->RetrievePropertyValueFromDataExpectString('Foo');
    }

    public static function DaftObjectIdProperties() : array
    {
        return ['Foo'];
    }
}
