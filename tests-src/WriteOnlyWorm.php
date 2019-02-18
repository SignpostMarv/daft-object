<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class WriteOnlyWorm extends WriteOnly implements DaftObjectWorm
{
    /**
    * @use DaftObjectIdValuesHashLazyInt<WriteOnlyWorm>
    */
    use DaftObjectIdValuesHashLazyInt;
}
