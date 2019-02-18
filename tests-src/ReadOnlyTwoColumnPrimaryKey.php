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
        return (string) $this->RetrievePropertyValueFromData('Foo');
    }

    public function GetBar() : float
    {
        /**
        * @var float|string $out
        */
        $out = $this->RetrievePropertyValueFromData('Bar');

        return is_string($out) ? ((float) $out) : $out;
    }

    public function GetBaz() : int
    {
        /**
        * @var int|string $out
        */
        $out = $this->RetrievePropertyValueFromData('Baz');

        return is_string($out) ? ((int) $out) : $out;
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
