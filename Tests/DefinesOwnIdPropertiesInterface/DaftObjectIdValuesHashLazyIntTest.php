<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinesOwnIdPropertiesInterface;

use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\ReadOnlyTwoColumnPrimaryKey;

class DaftObjectIdValuesHashLazyIntTest extends TestCase
{
    public function test_DaftObjectIdHash() : void
    {
        $a = new ReadOnlyTwoColumnPrimaryKey(['Foo' => 'bar', 'Bar' => 'foo']);
        $b = new ReadOnlyTwoColumnPrimaryKey(['Foo' => 'bar', 'Bar' => 'foo']);

        static::assertSame(
            ReadOnlyTwoColumnPrimaryKey::DaftObjectIdHash($a),
            ReadOnlyTwoColumnPrimaryKey::DaftObjectIdHash($b)
        );
    }
}
