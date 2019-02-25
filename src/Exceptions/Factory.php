<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Exceptions;

use SignpostMarv\DaftObject\DaftJson;
use SignpostMarv\DaftObject\DaftObject;
use SignpostMarv\SprintfExceptionFactory\SprintfExceptionFactory;
use Throwable;

abstract class Factory extends SprintfExceptionFactory
{
    const SPRINTF_PROPERTY_NOT_THINGABLE = 'Property not %s: %s::$%s';

    const SPRINTF_THING_DOES_NOT_IMPLEMENT_THING = '%s does not implement %s';

    /**
    * @psalm-param class-string $class
    * @psalm-param class-string $doesNotImplementClass
    */
    public static function ClassDoesNotImplementClassException(
        string $class,
        string $doesNotImplementClass,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : ClassDoesNotImplementClassException {
        /**
        * @var ClassDoesNotImplementClassException
        */
        $out = static::Exception(
            ClassDoesNotImplementClassException::class,
            $code,
            $previous,
            ClassDoesNotImplementClassException::class,
            self::SPRINTF_THING_DOES_NOT_IMPLEMENT_THING,
            $class,
            $doesNotImplementClass
        );

        return $out;
    }

    /**
    * @psalm-param class-string<DaftObject> $class
    */
    public static function DaftObjectNotDaftJsonBadMethodCallException(
        string $class,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : DaftObjectNotDaftJsonBadMethodCallException {
        /**
        * @var DaftObjectNotDaftJsonBadMethodCallException
        */
        $out = static::BadMethodCallException(
            DaftObjectNotDaftJsonBadMethodCallException::class,
            $code,
            $previous,
            self::SPRINTF_THING_DOES_NOT_IMPLEMENT_THING,
            $class,
            DaftJson::class
        );

        return $out;
    }

    /**
    * @psalm-param class-string $className
    */
    public static function NotPublicGetterPropertyException(
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : NotPublicGetterPropertyException {
        /**
        * @var NotPublicGetterPropertyException
        */
        $out = static::AbstractPropertyNotThingableException(
            NotPublicGetterPropertyException::class,
            'a public getter',
            $className,
            $property,
            $code,
            $previous
        );

        return $out;
    }

    /**
    * @psalm-param class-string $className
    */
    public static function NotPublicSetterPropertyException(
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : NotPublicSetterPropertyException {
        /**
        * @var NotPublicSetterPropertyException
        */
        $out = static::AbstractPropertyNotThingableException(
            NotPublicSetterPropertyException::class,
            'a public setter',
            $className,
            $property,
            $code,
            $previous
        );

        return $out;
    }

    /**
    * @psalm-param class-string $className
    */
    public static function PropertyNotJsonDecodableException(
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : PropertyNotJsonDecodableException {
        /**
        * @var PropertyNotJsonDecodableException
        */
        $out = static::AbstractPropertyNotThingableException(
            PropertyNotJsonDecodableException::class,
            'json-decodable',
            $className,
            $property,
            $code,
            $previous
        );

        return $out;
    }

    /**
    * @psalm-param class-string $className
    */
    public static function PropertyNotJsonDecodableShouldBeArrayException(
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : PropertyNotJsonDecodableShouldBeArrayException {
        /**
        * @var PropertyNotJsonDecodableShouldBeArrayException
        */
        $out = static::AbstractPropertyNotThingableException(
            PropertyNotJsonDecodableShouldBeArrayException::class,
            'json-decodable (should be an array)',
            $className,
            $property,
            $code,
            $previous
        );

        return $out;
    }

    /**
    * @psalm-param class-string $className
    */
    public static function PropertyNotNullableException(
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : PropertyNotNullableException {
        /**
        * @var PropertyNotNullableException
        */
        $out = static::AbstractPropertyNotThingableException(
            PropertyNotNullableException::class,
            'nullable',
            $className,
            $property,
            $code,
            $previous
        );

        return $out;
    }

    /**
    * @psalm-param class-string $className
    */
    public static function PropertyNotRewriteableException(
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : PropertyNotRewriteableException {
        /**
        * @var PropertyNotRewriteableException
        */
        $out = static::AbstractPropertyNotThingableException(
            PropertyNotRewriteableException::class,
            'rewriteable',
            $className,
            $property,
            $code,
            $previous
        );

        return $out;
    }

    /**
    * @psalm-param class-string<DaftObject> $className
    */
    public static function UndefinedPropertyException(
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : UndefinedPropertyException {
        /**
        * @var UndefinedPropertyException
        */
        $out = static::AbstractPropertyNotThingableException(
            UndefinedPropertyException::class,
            'defined',
            $className,
            $property,
            $code,
            $previous
        );

        return $out;
    }

    /**
    * @psalm-param class-string<AbstractPropertyNotThingableException> $type
    */
    protected static function AbstractPropertyNotThingableException(
        string $type,
        string $thing,
        string $className,
        string $property,
        int $code = self::DEFAULT_INT_CODE,
        Throwable $previous = null
    ) : AbstractPropertyNotThingableException {
        /**
        * @var AbstractPropertyNotThingableException
        */
        $out = static::Exception(
            $type,
            $code,
            $previous,
            AbstractPropertyNotThingableException::class,
            self::SPRINTF_PROPERTY_NOT_THINGABLE,
            $thing,
            $className,
            $property
        );

        return $out;
    }
}
