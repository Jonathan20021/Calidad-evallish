<?php

namespace App\Models;

use App\Config\PoncheDatabase;

class PoncheCampaign
{
    private $db;

    public function __construct()
    {
        $this->db = PoncheDatabase::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM campaigns ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM campaigns WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
