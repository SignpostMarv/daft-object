<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftJson;

use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\Exceptions\ClassDoesNotImplementClassException;
use SignpostMarv\DaftObject\JsonTypeUtilities;
use SignpostMarv\DaftObject\Tests\TestCase;

/**
* @template T as DaftJson
*
* @template-extends TestCase<T>
*/
class DaftJsonExceptionTest extends TestCase
{
    /**
    * @dataProvider dataProviderImplementations
    *
    * @psalm-param class-string<DaftObject> $type
    */
    public function test_ThrowIfNotJsonType(string $type) : void
    {
        if ( ! is_a($type, DaftJson::class, true)) {
            $this->expectException(ClassDoesNotImplementClassException::class);
            $this->expectExceptionMessage(sprintf(
                '%s does not implement %s',
                $type,
                DaftJson::class
            ));
        }

        $json_type = JsonTypeUtilities::ThrowIfNotJsonType($type);

        if (is_a($type, DaftJson::class, true)) {
            static::assertTrue(is_a($json_type, DaftJson::class, true));
        }
    }
}
