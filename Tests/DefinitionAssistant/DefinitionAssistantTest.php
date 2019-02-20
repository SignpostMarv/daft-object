<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use Error;
use InvalidArgumentException;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant as ParentDefinitionAssistant;
use SignpostMarv\DaftObject\AbstractDaftObject;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DateTimeImmutableTestObject;
use SignpostMarv\DaftObject\Tests\TestCase;

class DefinitionAssistantTest extends TestCase
{
    /**
    * @dataProvider dataProvider_DaftObject__has_properties
    *
    * @psalm-param class-string<DaftObject> $className
    */
    public function testIsTypeUnregistered(string $className) : void
    {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered($className));

        if (is_a($className, AbstractDaftObject::class, true)) {
            DefinitionAssistant::RegisterAbstractDaftObjectType($className);
            static::assertFalse(DefinitionAssistant::IsTypeUnregistered($className));
            static::assertGreaterThanOrEqual(
                count($className::DaftObjectProperties()),
                DefinitionAssistant::ObtainExpectedProperties($className)
            );

            static::assertFalse(DefinitionAssistant::IsTypeUnregistered(
                $className
            ));
            DefinitionAssistant::ClearTypes();
            DefinitionAssistant::RegisterAbstractDaftObjectType($className);
            static::assertGreaterThanOrEqual(
                $className::PROPERTIES,
                DefinitionAssistant::ObtainExpectedProperties(
                    $className
                )
            );
            DefinitionAssistant::ClearTypes();
        }
    }

    public function DataProviderExceptionsInRegisterType() : array
    {
        return [
            [
                DateTimeImmutableTestObject::class,
                ['foo' => 'foo'],
                Error::class,
                'Cannot unpack array with string keys',
            ],
        ];
    }

    /**
    * @param array<int, string> $properties
    *
    * @psalm-param class-string<AbstractDaftObject> $type
    *
    * @dataProvider DataProviderExceptionsInRegisterType
    */
    public function testExceptionsInRegisterType(
        string $type,
        array $properties,
        string $exception,
        string $message
    ) : void {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered($type));

        static::expectException($exception);
        static::expectExceptionMessage($message);

        static::assertSame(
            $type,
            DefinitionAssistant::public_RegisterDaftObjectTypeFromTypeAndProps(
                $type,
                ...$properties
            )
        );

        DefinitionAssistant::ClearTypes();

        static::assertSame(
            $type,
            DefinitionAssistant::public_MaybeRegisterAdditionalTypes($type)
        );
    }

    public function testRegisterAbstractDaftObjectTypeHasAlreadyBeenRegistered() : void
    {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(
            DefinesPropertyOnInterfaceClassImplementation::class
        ));

        static::assertNull(DefinitionAssistant::GetterMethodName(
            DefinesPropertyOnInterfaceClassImplementation::class,
            'foo'
        ));

        static::assertNull(DefinitionAssistant::GetterMethodName(
            DefinesPropertyOnInterfaceClassImplementation::class,
            'bar'
        ));

        static::assertNull(
            DefinitionAssistant::SetterOrGetterClosure(
                DefinesPropertyOnInterfaceClassImplementation::class,
                false,
                'bar'
            )('bar')
        );

        DefinitionAssistant::ClearTypes();

        DefinitionAssistant::RegisterAbstractDaftObjectType(
            DefinesPropertyOnInterfaceClassImplementation::class
        );

        static::assertSame('GetFoo', DefinitionAssistant::GetterMethodName(
            DefinesPropertyOnInterfaceClassImplementation::class,
            'foo'
        ));

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            ParentDefinitionAssistant::class .
            '::RegisterType()' .
            ' has already been registered!'
        );

        DefinitionAssistant::RegisterAbstractDaftObjectType(
            DefinesPropertyOnInterfaceClassImplementation::class
        );
    }
}
