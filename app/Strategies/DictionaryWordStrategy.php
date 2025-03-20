<?php

namespace App\Strategies;

use App\Interfaces\PasswordCrackerStrategy;
use App\Services\PasswordHasher;
use App\Interfaces\DatabaseInterface;

class DictionaryWordStrategy implements PasswordCrackerStrategy
{
    private PasswordHasher $hasher;
    private DatabaseInterface $userRepository;
    private string $dictionaryFile;
    private int $maxLength;

    public function __construct(
        PasswordHasher    $hasher,
        DatabaseInterface $userRepository,
        string            $dictionaryFile,
        int               $maxLength = 6
    )
    {
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
        $this->dictionaryFile = $dictionaryFile;
        $this->maxLength = $maxLength;
    }

    public function crack(): array
    {
        $start = hrtime(true);
        $results = [];

        // Check if dictionary file exists
        if (!file_exists($this->dictionaryFile)) {
            throw new \Exception("Dictionary file not found: {$this->dictionaryFile}");
        }

        // Get all users
        $users = $this->userRepository->getUsers();
        $userPasswords = [];

        foreach ($users as $user) {
            $userPasswords[$user['password']] = $user['user_id'];
        }

        // Read dictionary file
        $words = file($this->dictionaryFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($words as $word) {
            $hash = $this->hasher->hash($word);

            if (isset($userPasswords[$hash])) {
                $userId = $userPasswords[$hash];
                $results[] = [
                    'user_id' => $userId,
                    'password' => $word,
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
        return 'dictionary';
    }
}