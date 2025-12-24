<?php

namespace App\Model;

class MedicamentModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllActifs()
    {
        $stmt = $this->db->prepare('SELECT * FROM medicaments WHERE actif = 1 ORDER BY nom');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM medicaments WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare('
            INSERT INTO medicaments (nom, dose, type, frequence, heures_prise, actif, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['nom'],
            $data['dose'] ?? null,
            $data['type'] ?? 'regulier',
            $data['frequence'] ?? null,
            json_encode($data['heures_prise'] ?? []),
            $data['actif'] ?? 1,
            $data['notes'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare('
            UPDATE medicaments SET
                nom = ?, dose = ?, type = ?, frequence = ?, heures_prise = ?, actif = ?, notes = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $data['nom'],
            $data['dose'] ?? null,
            $data['type'] ?? 'regulier',
            $data['frequence'] ?? null,
            json_encode($data['heures_prise'] ?? []),
            $data['actif'] ?? 1,
            $data['notes'] ?? null,
            $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM medicaments WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function toggleActif($id)
    {
        $stmt = $this->db->prepare('UPDATE medicaments SET actif = NOT actif WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
