<?php
/**
* Exceptions.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Exceptions;

use Throwable;

/**
* Exception thrown when a property is not writeable.
*/
class PropertyNotJsonDecodableException extends AbstractPropertyNotThingableException
{
}
