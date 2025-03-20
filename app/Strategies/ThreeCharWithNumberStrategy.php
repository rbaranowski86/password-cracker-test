<?php

namespace App\Strategies;

use App\Interfaces\PasswordCrackerStrategy;
use App\Services\PasswordHasher;
use App\Interfaces\DatabaseInterface;

class ThreeCharWithNumberStrategy implements PasswordCrackerStrategy
{
    private PasswordHasher $hasher;
    private DatabaseInterface $userRepository;

    public function __construct(
        PasswordHasher    $hasher,
        DatabaseInterface $userRepository
    )
    {
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

        // Generate all possible combinations of 3 lowercase letters and 1 number
        $chars = range('A', 'Z');

        foreach ($chars as $char1) {
            foreach ($chars as $char2) {
                foreach ($chars as $char3) {
                    for ($i = 0; $i <= 9; $i++) {
                        $password = $char1 . $char2 . $char3 . $i;
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
                }
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
        return 'three_char';
    }
}
