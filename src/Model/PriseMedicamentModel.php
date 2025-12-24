<?php

namespace App\Model;

class PriseMedicamentModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPrisesForDate($medicamentId, $date)
    {
        $stmt = $this->db->prepare('
            SELECT periode, status FROM prises_medicaments
            WHERE medicament_id = ? AND date = ?
        ');
        $stmt->execute([$medicamentId, $date]);
        $result = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }

    public function getPrisesForMedicamentsDate($medicamentIds, $date)
    {
        if (empty($medicamentIds))
        {
            return [];
        }

        $placeholders = str_repeat('?,', count($medicamentIds) - 1) . '?';
        $stmt = $this->db->prepare("
            SELECT medicament_id, periode, status FROM prises_medicaments
            WHERE medicament_id IN ($placeholders) AND date = ?
        ");
        $params = array_merge($medicamentIds, [$date]);
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($result as $row)
        {
            $grouped[$row['medicament_id']][$row['periode']] = $row['status'];
        }

        return $grouped;
    }

    public function marquerPris($medicamentId, $date, $periode, $status = 'pris')
    {
        try
        {
            // Vérifier si une prise existe déjà
            $checkStmt = $this->db->prepare('SELECT id FROM prises_medicaments WHERE medicament_id = ? AND date = ? AND periode = ?');
            $checkStmt->execute([$medicamentId, $date, $periode]);
            $existingId = $checkStmt->fetchColumn();

            if ($existingId)
            {
                // Mettre à jour la prise existante
                $stmt = $this->db->prepare('UPDATE prises_medicaments SET status = ?, timestamp = datetime(\'now\') WHERE id = ?');
                $stmt->execute([$status, $existingId]);
            } else
            {
                // Insérer une nouvelle prise
                $stmt = $this->db->prepare('INSERT INTO prises_medicaments (medicament_id, date, periode, status) VALUES (?, ?, ?, ?)');
                $stmt->execute([$medicamentId, $date, $periode, $status]);
            }

            return true;
        } catch (Exception $e)
        {
            error_log('Erreur marquerPris: ' . $e->getMessage());

            return false;
        }
    }

    public function annulerPris($medicamentId, $date, $periode)
    {
        $stmt = $this->db->prepare("
            UPDATE prises_medicaments SET status = 'non', timestamp = datetime('now')
            WHERE medicament_id = ? AND date = ? AND periode = ?
        ");
        $stmt->execute([$medicamentId, $date, $periode]);

        return $stmt->rowCount() > 0;
    }

    public function getHistorique($userId, $startDate, $endDate)
    {
        // Assuming we add user_id to medicaments table later, for now skip user filter
        $stmt = $this->db->prepare('
            SELECT pm.*, m.nom, m.dose
            FROM prises_medicaments pm
            JOIN medicaments m ON pm.medicament_id = m.id
            WHERE pm.date BETWEEN ? AND ?
            ORDER BY pm.date DESC, pm.timestamp DESC
        ');
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getStats($userId, $startDate, $endDate)
    {
        // Total prises possibles vs prises
        // For simplicity, count all 'pris' in period
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_pris
            FROM prises_medicaments pm
            JOIN medicaments m ON pm.medicament_id = m.id
            WHERE pm.status = 'pris' AND pm.date BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['total_pris'] ?? 0;
    }
}
