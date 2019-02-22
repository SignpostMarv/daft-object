<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinesOwnIdPropertiesInterface\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftObjectIdValuesHashLazyInt;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;

/**
* @property-read int $id
*/
class DefinesOwnIntIdProperties extends AbstractArrayBackedDaftObject implements DefinesOwnIntegerIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<DefinesOwnIntIdProperties>
    */
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = ['id'];

    public function __construct(array $data = ['id' => 0], bool $writeAll = false)
    {
        if ( ! isset($data['id']) || ! is_int($data['id'])) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' was not array{id:int}!'
            );
        }

        $data = ['id' => $data['id']];

        parent::__construct($data, $writeAll);
    }

    public function GetId() : int
    {
        return (int) $this->RetrievePropertyValueFromData('id');
    }

    public static function DaftObjectIdProperties() : array
    {
        return self::PROPERTIES;
    }
}
