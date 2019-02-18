<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use Closure;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\DaftObject\DefinitionAssistant as Base;

class DefinitionAssistant extends Base
{
    public static function ClearTypes() : void
    {
        static::$properties = [];
        static::$getters = [];
        static::$setters = [];
    }

    public static function PublicSetterOrGetterClosure(
        string $type,
        bool $SetNotGet,
        string ...$props
    ) : Closure {
        return static::SetterOrGetterClosure($type, $SetNotGet, ...$props);
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
}
