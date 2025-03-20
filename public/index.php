<?php
@ini_set('memory_limit', '-1');
@ini_set('max_execution_time', 0);
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\UserRepository;
use App\Services\DatabaseFactory;
use App\Services\PasswordCracker;
use App\Services\PasswordHasher;
use App\Strategies\DictionaryWordStrategy;
use App\Strategies\MixedCharPasswordStrategy;
use App\Strategies\NumericPasswordStrategy;
use App\Strategies\ThreeCharWithNumberAssortedStrategy;
use App\Strategies\ThreeCharWithNumberStrategy;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Dictionary file path
$dictionaryFile = __DIR__ . '/../dictionary.txt';
$answersFile = __DIR__ . '/../answers.txt';

// API endpoint for password cracking
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    try {
// Create database connection
        $host = $_ENV['DB_HOST'];
        $database = $_ENV['DB_DATABASE'];
        $port = $_ENV['DB_PORT'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $pdo = DatabaseFactory::createConnection($host, $database, $port, $username, $password);

// Create dependencies
        $hasher = new PasswordHasher();
        $userRepository = new UserRepository($pdo);

// Create password cracker
        $cracker = new PasswordCracker();


// Add strategies
        $cracker->addStrategy(new NumericPasswordStrategy($hasher, $userRepository));
        $cracker->addStrategy(new ThreeCharWithNumberStrategy($hasher, $userRepository));
        $cracker->addStrategy(new ThreeCharWithNumberAssortedStrategy($hasher, $userRepository));
        $cracker->addStrategy(new DictionaryWordStrategy($hasher, $userRepository, $dictionaryFile));
        $cracker->addStrategy(new MixedCharPasswordStrategy($hasher, $userRepository));
// Process the request
        $result = [];

        switch ($_GET['action']) {
            case 'numeric':
                $result = $cracker->crackWithStrategy('numeric');
                break;

            case 'three_char':
                $result = $cracker->crackWithStrategy('three_char');
                break;

            case 'three_char_assorted':
                $result = $cracker->crackWithStrategy('three_char_assorted');
                break;

            case 'dictionary':
                $result = $cracker->crackWithStrategy('dictionary');
                break;

            case 'mixed':
                $result = $cracker->crackWithStrategy('mixed');
                break;

            case 'all':
                $result = $cracker->crackAll();
                break;

            case 'answers':
                $cracker->addStrategy(new DictionaryWordStrategy($hasher, $userRepository, $answersFile));
                $result = $cracker->crackWithStrategy('dictionary');
                break;

            default:
                $result = ['error' => 'Invalid action'];
        }

        echo json_encode($result, JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}