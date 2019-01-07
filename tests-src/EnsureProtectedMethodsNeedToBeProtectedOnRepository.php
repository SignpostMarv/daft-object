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
    */
    public function EnsureRecallDaftObjectFromData($id) : ? DaftObject
    {
        return $this->RecallDaftObjectFromData($id);
    }
}
