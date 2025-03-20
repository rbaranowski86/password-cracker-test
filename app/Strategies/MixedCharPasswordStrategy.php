<?php

namespace App\Strategies;

use App\Interfaces\PasswordCrackerStrategy;
use App\Services\PasswordHasher;
use App\Interfaces\DatabaseInterface;

class MixedCharPasswordStrategy implements PasswordCrackerStrategy
{
    private PasswordHasher $hasher;
    private DatabaseInterface $userRepository;

    private const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    private const LENGTH = 6;

    public function __construct(
        PasswordHasher $hasher,
        DatabaseInterface $userRepository
    ) {
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
    }

    public function crack(): array
    {
        $start = hrtime(true);
        $results = [];

        // Get all users
        $users = $this->userRepository->getUsers();
        $userPasswords = [];

        foreach ($users as $user) {
            $userPasswords[$user['password']] = $user['user_id'];
        }

        // Generate combinations and check hashes
        foreach ($this->generateCombinations(self::LENGTH, self::CHARSET) as $word) {
            $hash = $this->hasher->hash($word);

            if (isset($userPasswords[$hash])) {
                $userId = $userPasswords[$hash];
                $results[] = [
                    'user_id' => $userId,
                    'password' => $word,
                    'hash' => $hash
                ];
                echo "Found: $word\n";
            }
        }

        $end = hrtime(true);
        $duration = ($end - $start) / 1e9; // Convert to seconds

        return [
            'cracked' => $results,
            'duration' => $duration,
            'count' => count($results)
        ];
    }

    private function generateCombinations(int $length, string $charset = self::CHARSET): \Generator
    {
        $charsetLength = strlen($charset);
        $totalCombinations = pow($charsetLength, $length);

        for ($i = 0; $i < $totalCombinations; $i++) {
            $combination = '';
            $temp = $i;

            for ($j = 0; $j < $length; $j++) {
                $combination .= $charset[$temp % $charsetLength];
                $temp = (int)($temp / $charsetLength);
            }

            yield $combination;
        }
    }

    public function getName(): string
    {
        return 'mixed';
    }
}