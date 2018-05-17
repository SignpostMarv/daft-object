<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\ReadWrite;

$foo = new ReadWrite(['Foo' => 'bar']);

$foo->Foo = 'baz';

$foo->Foo = strrev($foo->Foo);
