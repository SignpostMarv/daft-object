<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftJson;

use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\LinkedData\HasArrayOfHasId;
use SignpostMarv\DaftObject\LinkedData\HasId;

/**
* @template T as DaftJson
*
* @template-extends TestCase<T>
*/
class DaftJsonTest extends TestCase
{
    public function test__json_decode() : void
    {
        $json = '{"@id":"foo"}';

        $obj = HasId::DaftObjectFromJsonString($json);

        static::assertSame('foo', $obj->ObtainId());
        static::assertSame('foo', $obj->__get('@id'));

        $obj = HasId::DaftObjectFromJsonArray([
            '@id' => 'foo',
        ]);

        static::assertSame('foo', $obj->ObtainId());
        static::assertSame('foo', $obj->__get('@id'));
    }

    public function test__json_encode() : void
    {
        $json = '{"json":[{"@id":"foo"},{"@id":"bar"}]}';

        $obj = new HasArrayOfHasId([
            'json' => [
                new HasId(['@id' => 'foo']),
                new HasId(['@id' => 'bar']),
            ],
        ]);

        static::assertSame($json, json_encode($obj));
        static::assertCount(2, $obj->json);
        static::assertNull($obj->single);

        list($a, $b) = $obj->json;

        static::assertInstanceOf(HasId::class, $a);
        static::assertInstanceOf(HasId::class, $b);

        static::assertSame('foo', $a->ObtainId());
        static::assertSame('foo', $a->__get('@id'));

        static::assertSame('bar', $b->ObtainId());
        static::assertSame('bar', $b->__get('@id'));

        $obj = HasArrayOfHasId::DaftObjectFromJsonString($json);

        static::assertSame($json, json_encode($obj));
        static::assertCount(2, $obj->json);
        static::assertNull($obj->single);

        list($a, $b) = $obj->json;

        static::assertInstanceOf(HasId::class, $a);
        static::assertInstanceOf(HasId::class, $b);

        static::assertSame('foo', $a->ObtainId());
        static::assertSame('foo', $a->__get('@id'));

        static::assertSame('bar', $b->ObtainId());
        static::assertSame('bar', $b->__get('@id'));

        $json = '{"json":[],"single":{"@id":"foo"}}';

        $obj = HasArrayOfHasId::DaftObjectFromJsonString($json);

        static::assertCount(0, $obj->json);

        $a = $obj->single;

        static::assertInstanceOf(HasId::class, $a);

        static::assertSame('foo', $a->ObtainId());
        static::assertSame('foo', $a->__get('@id'));
    }
}
