<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinesOwnIdPropertiesInterface\Fixtures;

use SignpostMarv\DaftObject\AbstractArrayBackedDaftObject;
use SignpostMarv\DaftObject\DaftObjectIdValuesHashLazyInt;
use SignpostMarv\DaftObject\DefinesOwnIntegerIdInterface;

/**
* @property-read int $id
*/
class DefinesOwnIntIdProperties  extends AbstractArrayBackedDaftObject implements DefinesOwnIntegerIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<DefinesOwnIntIdProperties>
    */
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = ['id'];

    /**
    * {@inheritdoc}
    *
    * @psalm-param array{id:int} $data
    */
    public function __construct(array $data = ['id' => 0], bool $writeAll = false)
    {
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
