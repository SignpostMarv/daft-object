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
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;

/**
* @template T as scalar
*
* @property-read scalar $id
*/
class DefinesOwnScalarIdProperties extends AbstractArrayBackedDaftObject implements DefinesOwnIdPropertiesInterface
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<DefinesOwnScalarIdProperties>
    */
    use DaftObjectIdValuesHashLazyInt;

    const PROPERTIES = ['id'];

    /**
    * {@inheritdoc}
    *
    * @psalm-param array{id:T} $data
    */
    public function __construct(array $data = ['id' => 0], bool $writeAll = false)
    {
        parent::__construct($data, $writeAll);
    }

    /**
    * @return scalar
    *
    * @psalm-return T
    */
    public function GetId()
    {
        /**
        * @var scalar
        *
        * @psalm-var T
        */
        $out = $this->RetrievePropertyValueFromData('id');

        return $out;
    }

    public static function DaftObjectIdProperties() : array
    {
        return self::PROPERTIES;
    }
}
