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
use SignpostMarv\DaftObject\DefinesOwnArrayIdInterface;

/**
* @property-read array $id
*/
class DefinesOwnArrayIdProperties extends AbstractArrayBackedDaftObject implements DefinesOwnArrayIdInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<DefinesOwnIntIdProperties>
    */
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = ['id'];

    /**
    * {@inheritdoc}
    *
    * @psalm-param array{id:scalar[]} $data
    */
    public function __construct(array $data = ['id' => ['foo' => 1, 'bar' => 2]], bool $writeAll = false)
    {
        parent::__construct($data, $writeAll);
    }

    /**
    * {@inheritdoc}
    *
    * @return scalar[]
    */
    public function GetId() : array
    {
        /**
        * @var scalar[]
        */
        $out = $this->RetrievePropertyValueFromData('id');

        return $out;
    }

    public static function DaftObjectIdProperties() : array
    {
        return self::PROPERTIES;
    }
}
