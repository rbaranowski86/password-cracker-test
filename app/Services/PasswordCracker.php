<?php

namespace App\Services;
use App\Interfaces\PasswordCrackerStrategy;
use Exception;

class PasswordCracker
{
    private array $strategies = [];
    private array $foundPasswords = [];

    public function addStrategy(PasswordCrackerStrategy $strategy): void
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    /**
     * @throws Exception
     */
    public function crackWithStrategy(string $strategyName): array
    {
        if (!isset($this->strategies[$strategyName])) {
            throw new Exception("Strategy not found: $strategyName");
        }

        return $this->strategies[$strategyName]->crack();
    }

    /**
     * @throws Exception
     */
    public function crackAll(): array
    {
        $results = [];

        foreach ($this->strategies as $name => $strategy) {
            $results[$name] = $this->crackWithStrategy($name);
        }

        return $results;
    }
}
