<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\NudgesIncorrectly;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\UndefinedPropertyException;
use SignpostMarv\DaftObject\WriteOnly;

class UndefinedPropertyExceptionTest extends TestCase
{
    public function dataProviderUndefinedPropertyException() : array
    {
        return [
            [
                WriteOnly::class,
                [
                    'nope' => 'foo',
                ],
                false,
                false,
                'nope',
            ],
        ];
    }

    /**
    * @dataProvider dataProviderUndefinedPropertyException
    *
    * @psalm-param class-string<DaftObject> $implementation
    */
    public function testUndefinedPropertyException(
        string $implementation,
        array $args,
        bool $getNotSet,
        bool $writeAll,
        string $property
    ) : void {
        $this->expectException(UndefinedPropertyException::class);
        $this->expectExceptionMessage(sprintf(
            'Property not defined: %s::$%s',
            $implementation,
            $property
        ));

        $obj = new $implementation($args, $writeAll);

        if ($getNotSet) {
            $foo = $obj->__get($property);
        } else {
            $obj->__set($property, 1);
        }
    }
}
