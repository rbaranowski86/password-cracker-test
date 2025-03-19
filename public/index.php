<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\UserRepository;
use App\Services\DatabaseFactory;
use App\Services\PasswordCracker;
use App\Services\PasswordHasher;
use App\Strategies\NumericPasswordStrategy;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

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

    $result = $cracker->crackWithStrategy('numeric');

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}