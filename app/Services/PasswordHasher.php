<?php

namespace App\Services;

class PasswordHasher
{
    private const SALT = 'ThisIs-A-Salt123';

    /**
     * Hash a password with the salt
     *
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public function hash(string $password): string
    {
        return md5($password . self::SALT);
    }
}