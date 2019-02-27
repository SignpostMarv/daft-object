<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @property-read string $Foo
* @property-read float $Bar
* @property-read bool|null $Bat
* @property-read int $Baz
* @property-read scalar[] $id
*/
class ReadOnlyTwoColumnPrimaryKey extends AbstractTestObject implements DefinesOwnArrayIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<ReadOnlyTwoColumnPrimaryKey>
    */
    use DaftObjectIdValuesHashLazyInt;

    /**
    * @return scalar[]
    */
    public function GetId() : array
    {
        return [
            $this->GetFoo(),
            $this->GetBar(),
        ];
    }

    /**
    * @return array<int, string>
    */
    public static function DaftObjectIdProperties() : array
    {
        return [
            'Foo',
            'Bar',
        ];
    }

    public function GetFoo() : string
    {
        return $this->RetrievePropertyValueFromDataExpectString('Foo');
    }

    public function GetBar() : float
    {
        return $this->RetrievePropertyValueFromDataExpectFloatish('Bar');
    }

    public function GetBaz() : int
    {
        return $this->RetrievePropertyValueFromDataExpectIntish('Baz');
    }

    public function GetBat() : ? bool
    {
        /**
        * @var bool|null|string $out
        */
        $out = $this->RetrievePropertyValueFromData('Bat');

        return is_string($out) ? ((bool) ((int) $out)) : $out;
    }
}
