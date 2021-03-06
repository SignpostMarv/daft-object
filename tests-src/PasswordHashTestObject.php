<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

/**
* @property-write string $password
* @property string $passwordHash
*/
final class PasswordHashTestObject extends AbstractArrayBackedDaftObject
{
    const PROPERTIES = [
        'password',
        'passwordHash',
    ];

    const CHANGE_OTHER_PROPERTIES = [
        'password' => [
            'passwordHash',
        ],
    ];

    protected function SetPasswordHash(string $hash) : void
    {
        $this->NudgePropertyValue('passwordHash', $hash);
    }

    public function SetPassword(string $password) : void
    {
        $this->SetPasswordHash((string) password_hash($password, PASSWORD_DEFAULT));
    }

    public function GetPasswordHash() : string
    {
        return TypeUtilities::ExpectRetrievedValueIsString(
            'passwordHash',
            $this->RetrievePropertyValueFromData('passwordHash'),
            static::class
        );
    }
}
