<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DaftObject;

use ReflectionMethod;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DaftObjectWorm;
use SignpostMarv\DaftObject\DefinesOwnArrayIdInterface;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;
use SignpostMarv\DaftObject\DefinesOwnStringIdInterface;
use SignpostMarv\DaftObject\PropertyNotNullableException;
use SignpostMarv\DaftObject\PropertyNotRewriteableException;
use SignpostMarv\DaftObject\ReadOnlyTwoColumnPrimaryKey;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\ReadWriteTwoColumnPrimaryKey;
use SignpostMarv\DaftObject\ReadWriteWorm;
use SignpostMarv\DaftObject\Tests\TestCase;
use SignpostMarv\DaftObject\UndefinedPropertyException;
use SignpostMarv\DaftObject\WriteOnly;
use SignpostMarv\DaftObject\WriteOnlyWorm;

class DaftTestObjectTest extends TestCase
{
    public function GoodDataProvider() : array
    {
        return [
            [
                ReadOnlyTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
                true,
                false,
            ],
            [
                ReadOnlyTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
                true,
                false,
            ],
            [
                ReadOnlyTwoColumnPrimaryKey::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                true,
                false,
            ],
            [
                WriteOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 1.0,
                    'Baz' => 2,
                    'Bat' => true,
                ],
                false,
                true,
            ],
            [
                WriteOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 2.0,
                    'Baz' => 3,
                    'Bat' => false,
                ],
                false,
                true,
            ],
            [
                WriteOnly::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                false,
                true,
            ],
            [
                WriteOnlyWorm::class,
                [
                    'Foo' => 'Foo',
                    'Bar' => 3.0,
                    'Baz' => 4,
                    'Bat' => null,
                ],
                false,
                true,
            ],
        ];
    }

    public function ThrowsExceptionProvider() : array
    {
        return [
            [
                WriteOnly::class,
                UndefinedPropertyException::class,
                (
                    'Property not defined: ' .
                    WriteOnly::class .
                    '::$NotFoo'
                ),
                [
                    'NotFoo' => 1,
                ],
                false,
                true,
            ],
        ];
    }

    public function DefinesOwnUntypedIdInterfaceProvider() : array
    {
        $out = [];

        /**
        * @var \Traversable<array<int, string|bool|array<string, scalar>>>
        */
        $implementations = $this->GoodDataProvider();

        foreach ($implementations as $args) {
            if (
                is_string($args[0]) &&
                ! is_a($args[0], DefinesOwnArrayIdInterface::class, true) &&
                ! is_a($args[0], DefinesOwnStringIdInterface::class, true) &&
                ! is_a($args[0], DefinesOwnIntegerIdInterface::class, true) &&
                true === $args[2]
            ) {
                $out[] = [$args[0], $args[1]];
            }
        }

        return $out;
    }

    /**
    * @param array<string, scalar|array|object|null> $params
    *
    * @dataProvider GoodDataProvider
    */
    public function testGood(
        string $implementation,
        array $params,
        bool $readable = false,
        bool $writeable = false
    ) : void {
        if ( ! is_subclass_of($implementation, DaftObject::class, true)) {
            static::markTestSkipped(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftObject::class
            );

            return;
        }

        /**
        * @var DaftObject
        */
        $obj = new $implementation($params, $writeable);

        if (true === $readable) {
            static::assertCount(($writeable ? count($params) : 0), $obj->ChangedProperties());

            foreach ($params as $k => $v) {
                static::assertSame(
                    $params[$k],
                    $obj->__get($k),
                    (
                        $implementation .
                        '::$' .
                        $k .
                        ' does not match supplied $params'
                    )
                );

                static::assertSame(
                    (is_null($params[$k]) ? false : true),
                    $obj->__isset($k),
                    (
                        $implementation .
                        '::$' .
                        $k .
                        ' was not found as ' .
                        (is_null($params[$k]) ? 'not set' : 'set')
                    )
                );
            }
        }

        foreach (array_keys($params) as $property) {
            static::assertSame(
                $writeable,
                $obj->HasPropertyChanged($property),
                (
                    $implementation .
                    '::$' .
                    $property .
                    ' was' .
                    ($writeable ? ' ' : ' not ') .
                    'writeable, property should' .
                    ($writeable ? ' ' : ' not ') .
                    'be changed'
                )
            );

            if ($writeable) {
                $obj->MakePropertiesUnchanged($property);
                static::assertFalse($obj->HasPropertyChanged($property));

                if (
                    in_array(
                        $property,
                        (array) $implementation::DaftObjectNullableProperties(),
                        true
                    )
                ) {
                    if ($obj instanceof DaftObjectWorm) {
                        $this->expectException(PropertyNotRewriteableException::class);
                        $this->expectExceptionMessage(sprintf(
                            'Property not rewriteable: %s::$%s',
                            $implementation,
                            $property
                        ));
                    }
                    $obj->__unset($property);
                    $obj->__set($property, null);
                }
            }
        }

        $obj->MakePropertiesUnchanged(...array_keys($params));

        $debugInfo = $this->VarDumpDaftObject($obj);

        /**
        * @var array<string, scalar|array|object|bool|float|null>
        */
        $props = [];

        foreach ($obj::DaftObjectExportableProperties() as $prop) {
            $expectedMethod = static::MethodNameFromProperty($prop);
            if (
                $obj->__isset($prop) &&
                method_exists($obj, $expectedMethod) &&
                (
                    new ReflectionMethod($obj, $expectedMethod)
                )->isPublic()
            ) {
                $props[$prop] = $obj->__get($prop);
            }
        }

        $regex =
            '/(?:class |object\()' .
            preg_quote(get_class($obj), '/') .
            '[\)]{0,1}#' .
            '\d+ \(' .
            preg_quote((string) count($props), '/') .
            '\) \{.+';

        foreach ($props as $prop => $val) {
            $regex .=
                ' (?:public ' .
                preg_quote('$' . $prop, '/') .
                '|' .
                preg_quote('["' . $prop . '"]', '/') .
                ')[ ]{0,1}' .
                preg_quote('=', '/') .
                '>.+' .
                (
                    is_int($val)
                        ? 'int'
                        : (
                            is_bool($val)
                                ? 'bool'
                                : (
                                    is_float($val)
                                        ? '(?:float|double)'
                                        : preg_quote(gettype($val), '/')
                                )
                        )
                ) .
                preg_quote(
                    (
                        '(' .
                        (
                            is_string($val)
                                ? mb_strlen($val, '8bit')
                                : (
                                    is_numeric($val)
                                        ? (string) $val
                                        : var_export($val, true)
                                )
                        ) .
                        ')' .
                        (
                            is_string($val)
                                ? (' "' . $val . '"')
                                : ''
                        )
                    ),
                    '/'
                ) .
                '.+';
        }

        $regex .= '\}.+$/s';

        static::assertRegExp($regex, str_replace("\n", ' ', $debugInfo));
    }

    /**
    * @psalm-param class-string<DaftObject> $implementation
    *
    * @dataProvider ThrowsExceptionProvider
    */
    public function testThrowsException(
        string $implementation,
        string $expectedExceptionType,
        string $expectedExceptionMessage,
        array $params,
        bool $readable,
        bool $writeable
    ) : void {
        $initialCount = count($params);

        $params = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);

        static::assertCount($initialCount, $params);

        if ($readable) {
            $this->expectException($expectedExceptionType);
            $this->expectExceptionMessage($expectedExceptionMessage);
            $obj = new $implementation($params, $writeable);

            /**
            * @var array<int, string>
            */
            $paramKeys = array_keys($params);

            foreach ($paramKeys as $property) {
                $var = $obj->__get($property);
            }
        } elseif ($writeable) {
            $this->expectException($expectedExceptionType);
            $this->expectExceptionMessage($expectedExceptionMessage);
            $obj = new $implementation($params, $writeable);
        }
    }

    /**
    * @psalm-suppress ForbiddenCode
    */
    final protected function VarDumpDaftObject(DaftObject $obj) : string
    {
        ob_start();
        var_dump($obj);

        return (string) ob_get_clean();
    }
}
