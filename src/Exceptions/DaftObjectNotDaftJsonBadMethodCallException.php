<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Exceptions;

use BadMethodCallException;
use SignpostMarv\DaftObject\DaftJson;
use Throwable;

class DaftObjectNotDaftJsonBadMethodCallException extends BadMethodCallException
{
}
