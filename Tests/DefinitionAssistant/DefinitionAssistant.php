<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DefinitionAssistant as Base;

/**
* @template T as DaftObject
*
* @template-extends Base<T>
*/
class DefinitionAssistant extends Base
{
    public static function ClearTypes() : void
    {
        static::$properties = [];
        static::$getters = [];
        static::$setters = [];
    }

    /**
    * @psalm-param class-string<DaftObject> $maybe
    */
    public static function public_RegisterDaftObjectTypeFromTypeAndProps(
        string $maybe,
        string $prop,
        string ...$props
    ) : string {
        return static::RegisterDaftObjectTypeFromTypeAndProps(
            $maybe,
            $prop,
            ...$props
        );
    }

    /**
    * @psalm-param class-string<T> $maybe
    *
    * @psalm-return class-string<T>
    */
    public static function public_MaybeRegisterAdditionalTypes(string $maybe) : string
    {
        return static::MaybeRegisterAdditionalTypes($maybe);
    }
}
