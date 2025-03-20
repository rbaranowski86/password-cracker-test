<?php

namespace App\Strategies;

use App\Interfaces\PasswordCrackerStrategy;
use App\Services\PasswordHasher;
use App\Interfaces\DatabaseInterface;

class ThreeCharWithNumberAssortedStrategy implements \App\Interfaces\PasswordCrackerStrategy
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
        $fourCharVariations = $this->generateFourCharVariations(range('A', 'Z'), range(0, 9));

        foreach ($fourCharVariations as $password) {
            $hash = $this->hasher->hash($password);

            //echo "Checking password: $password\n";

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

    private function generateFourCharVariations($letters, $digits): array
    {
        $variations = [];

        // All possible characters
        $allChars = array_merge($letters, $digits);

        // Generate all combinations
        for ($i = 0; $i < count($allChars); $i++) {
            for ($j = 0; $j < count($allChars); $j++) {
                for ($k = 0; $k < count($allChars); $k++) {
                    for ($l = 0; $l < count($allChars); $l++) {
                        $variation = $allChars[$i] . $allChars[$j] . $allChars[$k] . $allChars[$l];

                        // Check if the variation has exactly 3 letters and 1 digit
                        $letterCount = 0;
                        $digitCount = 0;

                        for ($m = 0; $m < 4; $m++) {
                            $char = $variation[$m];
                            if (in_array($char, $letters)) {
                                $letterCount++;
                            } else {
                                $digitCount++;
                            }
                        }

                        if ($letterCount === 3 && $digitCount === 1) {
                            $variations[] = $variation;
                        }
                    }
                }
            }
        }

        return $variations;
    }

    public function getName(): string
    {
        return 'three_char_assorted';
    }

}