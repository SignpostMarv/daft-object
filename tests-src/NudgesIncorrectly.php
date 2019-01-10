<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class NudgesIncorrectly extends AbstractTestObject implements DefinesOwnIdPropertiesInterface
{
    use DaftObjectIdValuesHashLazyInt;
    use ReadTrait, WriteTrait, DefineIdPropertiesCorrectlyTrait;

    public function SetFoo(string $value)
    {
        $this->NudgePropertyValue('nope', $value);
    }
}
