<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class FormField
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByTemplate($templateId)
    {
        $stmt = $this->db->prepare("SELECT * FROM form_fields WHERE template_id = ? ORDER BY field_order");
        $stmt->execute([$templateId]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO form_fields (template_id, label, field_type, options, max_score, weight, field_order, required) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['template_id'],
            $data['label'],
            $data['field_type'],
            $data['options'] ?? null,
            $data['max_score'] ?? 10,
            $data['weight'] ?? 1.00,
            $data['field_order'] ?? 0,
            $data['required'] ?? 1
        ]);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM form_fields WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deleteByTemplate($templateId)
    {
        $stmt = $this->db->prepare("DELETE FROM form_fields WHERE template_id = ?");
        return $stmt->execute([$templateId]);
    }
}
