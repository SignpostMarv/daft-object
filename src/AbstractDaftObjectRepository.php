<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

abstract class AbstractDaftObjectRepository implements DaftObjectRepository
{
    /**
    * @var DefinesOwnIdPropertiesInterface[]
    */
    protected $memory = [];

    /**
    * @var array<string, array<string, mixed>>
    */
    protected $data = [];

    /**
    * @var string
    */
    protected $type;

    /**
    * @var mixed[]|null
    */
    protected $args;

    /**
    * @param mixed ...$args
    */
    protected function __construct(string $type, ...$args)
    {
        $this->type = $type;
        $this->args = $args;
        unset($this->args);
    }

    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object)
    {
        TypeParanoia::ThrowIfNotDaftObjectIdPropertiesType(
            $object,
            1,
            static::class,
            __FUNCTION__,
            $this->type
        );

        $id = [];

        /**
        * @var array<int, string>
        */
        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            $id[] = $object->__get($prop);
        }

        $this->ForgetDaftObjectById($id);
    }

    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object)
    {
        TypeParanoia::ThrowIfNotDaftObjectIdPropertiesType(
            $object,
            1,
            static::class,
            __FUNCTION__,
            $this->type
        );

        $id = [];

        /**
        * @var array<int, string>
        */
        $properties = $object::DaftObjectIdProperties();

        foreach ($properties as $prop) {
            $id[] = $object->__get($prop);
        }

        $this->RemoveDaftObjectById($id);
    }

    /**
    * {@inheritdoc}
    */
    public static function DaftObjectRepositoryByType(
        string $type,
        ...$args
    ) : DaftObjectRepository {
        TypeParanoia::ThrowIfNotType(
            $type,
            1,
            static::class,
            __FUNCTION__,
            DaftObjectCreatedByArray::class,
            DefinesOwnIdPropertiesInterface::class
        );

        return new static($type, ...$args);
    }

    /**
    * {@inheritdoc}
    */
    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object,
        ...$args
    ) : DaftObjectRepository {
        return static::DaftObjectRepositoryByType(get_class($object), ...$args);
    }
}
