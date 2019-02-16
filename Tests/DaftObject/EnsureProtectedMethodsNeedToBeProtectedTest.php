<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use SignpostMarv\DaftObject\EnsureProtectedMethodsNeedToBeProtectedOnAbstractDaftObject;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\UndefinedPropertyException;

class EnsureProtectedMethodsNeedToBeProtectedTest extends TestCase
{
    public function testEnsureMaybeThrowOnDoGetSet() : void
    {
        $obj = new EnsureProtectedMethodsNeedToBeProtectedOnAbstractDaftObject();

        static::expectException(UndefinedPropertyException::class);

        $obj->EnsureMaybeThrowOnDoGetSet('foo', true, []);
    }
}
