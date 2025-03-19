<?php

namespace App\Interfaces;

interface DatabaseInterface
{
    /**
     * Get all users with their passwords
     *
     * @return array List of users with their passwords
     */
    public function getUsers(): array;
}