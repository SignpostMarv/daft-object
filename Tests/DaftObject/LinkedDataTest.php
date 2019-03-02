<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use SignpostMarv\DaftObject\LinkedData\HasId;
use SignpostMarv\DaftObject\Tests\TestCase;

class LinkedDataTest extends TestCase
{
    public function testJsonEncode() : void
    {
        $arr = ['@id' => 'foo'];

        $foo = new HasId($arr);

        static::assertSame('{"@id":"foo"}', json_encode($foo));

        $bar = HasId::DaftObjectFromJsonString('{"@id":"foo"}');

        static::assertInstanceOf(HasId::class, $bar);
        static::assertSame('{"@id":"foo"}', json_encode($bar));

        $bar->__set('@id', 'bar');
        static::assertSame('{"@id":"bar"}', json_encode($bar));
    }
}
