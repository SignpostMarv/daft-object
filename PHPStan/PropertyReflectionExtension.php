<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\PHPStan;

use InvalidArgumentException;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use ReflectionMethod;
use SignpostMarv\DaftObject\DaftObject;

class PropertyReflectionExtension implements PropertyReflection
{
    /**
    * @var Type
    */
    private $type;

    /**
    * @var Broker
    */
    private $broker;

    /**
    * @var bool
    */
    private $readable = false;

    /**
    * @var bool
    */
    private $writeable = false;

    /**
    * @var bool
    */
    private $public;

    /**
    * @var ClassReflection
    */
    private $readableDeclaringClass;

    /**
    * @var ClassReflection
    */
    private $writeableDeclaringClass;

    public function __construct(
        ClassReflection $classReflection,
        Broker $broker,
        string $propertyName
    ) {
        if (
            false === is_a(
                $classReflection->getName(),
                DaftObject::class,
                true
            )
        ) {
            throw new InvalidArgumentException(
                $classReflection->getName() .
                ' is not an implementation of ' .
                DaftObject::class
            );
        }

        $this->broker = $broker;

        $className = $classReflection->getName();

        $this->public =
            in_array(
                $propertyName,
                $className::DaftObjectPublicGetters(),
                true
            ) ||
            in_array(
                $propertyName,
                $className::DaftObjectPublicSetters(),
                true
            );

        $this->type = new MixedType();

        $getter = 'Get' . ucfirst($propertyName);
        $setter = 'Set' . ucfirst($propertyName);

        $this->readableDeclaringClass = $classReflection;
        $this->writeableDeclaringClass = $classReflection;

        if ($classReflection->getNativeReflection()->hasMethod($getter)) {
            $refMethod = new ReflectionMethod($className, $getter);

            $this->readableDeclaringClass = $this->SetGetterProps($refMethod);
        }

        if ($classReflection->getNativeReflection()->hasMethod($setter)) {
            $refMethod = new ReflectionMethod($className, $setter);

            $this->writeableDeclaringClass = $this->SetSetterProps(
                $className,
                $refMethod
            );
        }
    }

    public function getType() : Type
    {
        return $this->type;
    }

    public function isReadable() : bool
    {
        return $this->readable;
    }

    public function isWritable() : bool
    {
        return $this->writeable;
    }

    public function isPublic() : bool
    {
        return $this->public;
    }

    public function isPrivate() : bool
    {
        return false === $this->isPublic();
    }

    public function isStatic() : bool
    {
        return false;
    }

    public function getDeclaringClass() : ClassReflection
    {
        if ($this->readable) {
            return $this->readableDeclaringClass;
        }

        return $this->writeableDeclaringClass;
    }

    private function SetGetterProps(
        ReflectionMethod $refMethod
    ) : ClassReflection {
        $this->readable = true;

        if ($refMethod->isStatic()) {
            throw new InvalidArgumentException(
                'Implementations of ' .
                DaftObject::class .
                ' must not contain static getters.'
            );
        }

        if ($refMethod->hasReturnType()) {
            $this->type = TypehintHelper::decideTypeFromReflection(
                $refMethod->getReturnType()
            );
        }

        return static::DetermineDeclaringClass(
            $this->broker,
            $refMethod
        );
    }

    private function SetSetterProps(
        string $className,
        ReflectionMethod $refMethod
    ) : ClassReflection {
        $this->writeable = true;

        if ($refMethod->getNumberOfRequiredParameters() < 1) {
            throw new InvalidArgumentException(
                'Implementations of ' .
                DaftObject::class .
                ' must require at least one parameter on all setters!'
            );
        }

        $refParam = $refMethod->getParameters()[0];

        if ($refParam->hasType()) {
            $this->type = TypehintHelper::decideTypeFromReflection(
                $refParam->getType(),
                null,
                $className,
                false
            );
        }

        return static::DetermineDeclaringClass(
            $this->broker,
            $refMethod
        );
    }

    private static function DetermineDeclaringClass(
        Broker $broker,
        ReflectionMethod $refMethod
    ) : ClassReflection {
        return $broker->getClassFromReflection(
            $refMethod->getDeclaringClass(),
            $refMethod->getDeclaringClass()->getName(),
            $refMethod->getDeclaringClass()->isAnonymous()
        );
    }
}