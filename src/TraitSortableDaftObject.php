<?php
/**
* Exceptions.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @template T as DaftSortableObject
*/
trait TraitSortableDaftObject
{
    /**
    * @return scalar|array|object|null
    */
    abstract public function __get(string $property);

    /**
    * @psalm-param T as $otherObject
    */
    public function CompareToDaftSortableObject(DaftSortableObject $otherObject) : int
    {
        foreach (static::DaftSortableObjectProperties() as $property) {
            $sort = $this->__get($property) <=> $otherObject->__get($property);

            if (0 !== $sort) {
                return $sort;
            }
        }

        return 0;
    }

    /**
    * @return array<int, string>
    */
    public static function DaftSortableObjectProperties() : array
    {
        if ( ! is_a(static::class, DaftSortableObject::class, true)) {
            throw Exceptions\Factory::ClassDoesNotImplementClassException(
                static::class,
                DaftSortableObject::class
            );
        }

        /**
        * @var array<int, string>
        */
        $out = constant(static::class . '::SORTABLE_PROPERTIES');

        return $out;
    }
}
