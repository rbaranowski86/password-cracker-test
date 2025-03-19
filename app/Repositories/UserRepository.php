<?php

namespace App\Repositories;

use App\Interfaces\DatabaseInterface;
use PDO;

class UserRepository implements DatabaseInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getUsers(): array
    {
        $stmt = $this->db->query("SELECT user_id, password FROM not_so_smart_users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
