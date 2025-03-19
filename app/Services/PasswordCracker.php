<?php

namespace App\Services;
use App\Interfaces\PasswordCrackerStrategy;

class PasswordCracker
{
    private array $strategies = [];
    private array $foundPasswords = [];

    public function addStrategy(PasswordCrackerStrategy $strategy): void
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    public function crackWithStrategy(string $strategyName): array
    {
        if (!isset($this->strategies[$strategyName])) {
            throw new \Exception("Strategy not found: $strategyName");
        }

        $result = $this->strategies[$strategyName]->crack();

        // Store found passwords
        foreach ($result['cracked'] as $crackedPassword) {
            $this->foundPasswords[$crackedPassword['user_id']] = $crackedPassword['password'];
        }

        return $result;
    }

    public function crackAll(): array
    {
        $results = [];

        foreach ($this->strategies as $name => $strategy) {
            $results[$name] = $this->crackWithStrategy($name);
        }

        return $results;
    }

    public function getFoundPasswords(): array
    {
        return $this->foundPasswords;
    }
}
