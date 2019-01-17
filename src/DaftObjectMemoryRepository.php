<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class DaftObjectMemoryRepository extends AbstractDaftObjectRepository
{
    const BOOL_DEFAULT_ASSUMEDOESNOTEXIST = false;

    /**
    * @var DefinesOwnIdPropertiesInterface[]
    */
    protected $memory = [];

    /**
    * @var mixed[][]
    */
    protected $data = [];

    public function RememberDaftObject(DefinesOwnIdPropertiesInterface $object) : void
    {
        TypeParanoia::ThrowIfNotDaftObjectIdPropertiesType(
            $object,
            1,
            static::class,
            __FUNCTION__,
            $this->type
        );

        $hashId = $object::DaftObjectIdHash($object);

        $this->memory[$hashId] = $object;

        $this->RememberDaftObjectData($object);
    }

    /**
    * @param mixed $id
    */
    public function ForgetDaftObjectById($id) : void
    {
        $this->ForgetDaftObjectByHashId($this->ObjectHashId($id));
    }

    /**
    * @param mixed $id
    */
    public function RemoveDaftObjectById($id) : void
    {
        $this->RemoveDaftObjectByHashId($this->ObjectHashId($id));
    }

    /**
    * @param mixed $id
    */
    public function RecallDaftObject($id) : ? DaftObject
    {
        $hashId = $this->ObjectHashId($id);

        if (false === isset($this->memory[$hashId])) {
            return $this->RecallDaftObjectFromData($id);
        }

        return $this->memory[$hashId];
    }

    public function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object,
        bool $assumeDoesNotExist = self::BOOL_DEFAULT_ASSUMEDOESNOTEXIST
    ) : void {
        $hashId = $object::DaftObjectIdHash($object);

        $this->data[$hashId] = [];

        foreach ($object::DaftObjectPublicGetters() as $property) {
            $this->data[$hashId][$property] = $object->__get($property);
        }
    }

    /**
    * Recalls the corresponding daft object instance from cached data.
    *
    * @param mixed $id
    */
    protected function RecallDaftObjectFromData($id) : ? DaftObject
    {
        $hashId = $this->ObjectHashId($id);

        if (isset($this->data[$hashId])) {
            $type = $this->type;

            /**
            * @var DaftObject
            */
            $out = new $type($this->data[$hashId]);

            return $out;
        }

        return null;
    }

    /**
    * Converts an id to a unique-enough-for-now string.
    *
    * @param mixed $id
    */
    private function ObjectHashId($id) : string
    {
        return TypeParanoia::EnsureArgumentIsString(
            $this->type::DaftObjectIdValuesHash(
                TypeParanoia::ForceArgumentAsArray($id)
            )
        );
    }

    private function ForgetDaftObjectByHashId(string $hashId) : void
    {
        if (true === isset($this->memory[$hashId])) {
            unset($this->memory[$hashId]);
        }
    }

    private function RemoveDaftObjectByHashId(string $hashId) : void
    {
        $this->ForgetDaftObjectByHashId($hashId);

        if (true === isset($this->data[$hashId])) {
            unset($this->data[$hashId]);
        }
    }
}
