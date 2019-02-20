<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftJson;

use SignpostMarv\DaftObject\LinkedData\HasArrayOfHasId;
use SignpostMarv\DaftObject\LinkedData\HasId;
use SignpostMarv\DaftObject\Tests\TestCase;

class DaftJsonTest extends TestCase
{
    public function test__json_decode() : void
    {
        $json = '{"@id":"foo"}';

        $obj = HasId::DaftObjectFromJsonString($json);

        static::assertSame(json_decode($json, true), $obj->jsonSerialize());

        static::assertInstanceOf(HasId::class, $obj);
        static::assertSame('foo', $obj->ObtainId());
        static::assertSame('foo', $obj->__get('@id'));

        $obj = HasId::DaftObjectFromJsonArray([
            '@id' => 'foo',
        ]);

        static::assertInstanceOf(HasId::class, $obj);
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

        static::assertSame('foo', $a->ObtainId());
        static::assertSame('foo', $a->__get('@id'));

        static::assertSame('bar', $b->ObtainId());
        static::assertSame('bar', $b->__get('@id'));

        $obj = HasArrayOfHasId::DaftObjectFromJsonString($json);

        static::assertInstanceOf(HasArrayOfHasId::class, $obj);
        static::assertSame($json, json_encode($obj));
        static::assertCount(2, $obj->json);
        static::assertNull($obj->single);

        list($a, $b) = $obj->json;

        static::assertSame('foo', $a->ObtainId());
        static::assertSame('foo', $a->__get('@id'));

        static::assertSame('bar', $b->ObtainId());
        static::assertSame('bar', $b->__get('@id'));

        $obj->json = [];

        static::assertCount(0, $obj->json);

        $obj->json = [$b, $a];

        static::assertCount(2, $obj->json);
        static::assertSame($a, $obj->json[1]);
        static::assertSame($b, $obj->json[0]);

        $json = '{"json":[],"single":{"@id":"foo"}}';

        $obj = HasArrayOfHasId::DaftObjectFromJsonString($json);

        static::assertInstanceOf(HasArrayOfHasId::class, $obj);
        static::assertCount(0, $obj->json);

        $a = $obj->single;

        static::assertInstanceOf(HasId::class, $a);

        static::assertSame('foo', $a->ObtainId());
        static::assertSame('foo', $a->__get('@id'));

        $obj->single = null;

        static::assertSame('NULL', gettype($obj->single));

        $obj->single = $a;

        static::assertSame($a, $obj->single);
    }
}
