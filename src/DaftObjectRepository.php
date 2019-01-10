<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftObjectRepository
{
    public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object);

    /**
    * Allow data to be persisted without assuming the object exists, i.e. if it has no id yet.
    */
    public function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object,
        bool $assumeDoesNotExist = false
    );

    public function ForgetDaftObject(DefinesOwnIdPropertiesInterface $object);

    /**
    * @param mixed $id
    */
    public function ForgetDaftObjectById($id);

    public function RemoveDaftObject(DefinesOwnIdPropertiesInterface $object);

    /**
    * @param mixed $id
    */
    public function RemoveDaftObjectById($id);

    /**
    * @param mixed $id
    *
    * @return DaftObject|null
    */
    public function RecallDaftObject($id);

    /**
    * @param mixed ...$args
    *
    * @return static
    */
    public static function DaftObjectRepositoryByType(string $type, ...$args) : self;

    /**
    * @param mixed ...$args
    *
    * @return static
    */
    public static function DaftObjectRepositoryByDaftObject(
        DefinesOwnIdPropertiesInterface $object,
        ...$args
    ) : self;
}
