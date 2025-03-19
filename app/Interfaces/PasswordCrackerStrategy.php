<?php

namespace App\Interfaces;

interface PasswordCrackerStrategy
{
    /**
     * Attempt to crack passwords using a specific strategy
     *
     * @return array Results of the cracking attempt
     */
    public function crack(): array;

    /**
     * Get the name of this strategy
     *
     * @return string Strategy name
     */
    public function getName(): string;
}