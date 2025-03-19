<?php

namespace App\Strategies;

use App\Interfaces\DatabaseInterface;
use App\Services\PasswordHasher;

class NumericPasswordStrategy implements \App\Interfaces\PasswordCrackerStrategy
{
    private PasswordHasher $hasher;
    private DatabaseInterface $userRepository;
    private int $length;

    public function __construct(
        PasswordHasher $hasher,
        DatabaseInterface $userRepository,
        int $length = 5
    ) {
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
        $this->length = $length;
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

        // Test all possible numeric combinations
        $max = pow(10, $this->length);

        for ($i = 0; $i < $max; $i++) {
            $password = str_pad((string)$i, $this->length, '0', STR_PAD_LEFT);
            $hash = $this->hasher->hash($password);

            if (isset($userPasswords[$hash])) {
                $userId = $userPasswords[$hash];
                $results[] = [
                    'user_id' => $userId,
                    'password' => $password,
                    'hash' => $hash
                ];
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

    public function getName(): string
    {
        return 'numeric';
    }
}