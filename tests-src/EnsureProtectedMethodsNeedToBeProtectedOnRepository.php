<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class EnsureProtectedMethodsNeedToBeProtectedOnRepository extends DaftObjectMemoryRepository
{
    /**
    * @param mixed $id
    *
    * @return DaftObject|null
    */
    public function EnsureRecallDaftObjectFromData($id)
    {
        return $this->RecallDaftObjectFromData($id);
    }

    public static function EnsureConstructorNeedsToBeProtected(
        string $type,
        ...$args
    ) : AbstractDaftObjectRepository {
        return new static($type, ...$args);
    }
}
