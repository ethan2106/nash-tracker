<?php

namespace App\Repository;

use App\Model\Database;
use PDO;

/**
 * FoodRepository - Implémentation des opérations sur les aliments sauvegardés.
 */
class FoodRepository implements FoodRepositoryInterface
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * Recherche des aliments sauvegardés par nom.
     */
    public function searchSavedFoods(string $query): array
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT * FROM aliments
                WHERE LOWER(nom) LIKE LOWER(?)
                ORDER BY nom ASC
                LIMIT 20
            ');
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e)
        {
            error_log("Erreur lors de la recherche d'aliments sauvegardés: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Récupère tous les aliments sauvegardés avec pagination.
     */
    public function getSavedFoods(?int $limit = null, int $offset = 0): array
    {
        try
        {
            $sql = 'SELECT * FROM aliments ORDER BY nom ASC';

            if ($limit !== null)
            {
                $limit = (int)$limit;
                $offset = (int)$offset;
                $sql .= " LIMIT {$limit} OFFSET {$offset}";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e)
        {
            error_log('Erreur lors de la récupération des aliments sauvegardés: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Compte le nombre total d'aliments sauvegardés.
     */
    public function countSavedFoods(): int
    {
        try
        {
            $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM aliments');
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);
        } catch (\Exception $e)
        {
            error_log('Erreur lors du comptage des aliments sauvegardés: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Sauvegarde un aliment depuis l'API OpenFoodFacts.
     */
    public function saveFoodFromAPI(array $data): bool
    {
        try
        {
            // Vérifier si le nom du produit est fourni
            if (empty($data['product_name']))
            {
                return false;
            }

            // Vérifier si l'aliment existe déjà
            if (!empty($data['code']) && $this->foodExistsByBarcode($data['code']))
            {
                return true; // Considérer comme succès si déjà existant
            }

            // Préparer les autres_infos en JSON
            $autresInfos = [
                'marque' => $data['brands'] ?? '',
                'image_url' => $data['image_url'] ?? '',
                'code_barre' => $data['code'] ?? '',
                'source' => 'openfoodfacts',
            ];

            // Insérer le nouvel aliment
            $stmt = $this->db->prepare('
                INSERT INTO aliments (
                    nom, category_id, calories_100g, proteines_100g, glucides_100g,
                    sucres_100g, lipides_100g, acides_gras_satures_100g, fibres_100g,
                    sodium_100g, openfoodfacts_id, image_path, autres_infos
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $data['product_name'] ?? '',
                null, // category_id
                $data['nutriments']['energy-kcal_100g'] ?? 0,
                $data['nutriments']['proteins_100g'] ?? 0,
                $data['nutriments']['carbohydrates_100g'] ?? 0,
                $data['nutriments']['sugars_100g'] ?? 0,
                $data['nutriments']['fat_100g'] ?? 0,
                $data['nutriments']['saturated-fat_100g'] ?? 0,
                $data['nutriments']['fiber_100g'] ?? 0,
                $data['nutriments']['sodium_100g'] ?? 0,
                $data['code'] ?? '',
                $data['image_path'] ?? null,
                json_encode($autresInfos),
            ]);

            return true;
        } catch (\Exception $e)
        {
            error_log("Erreur lors de la sauvegarde d'aliment depuis API: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Supprime un aliment par son ID.
     */
    public function deleteFood(int $foodId): bool
    {
        try
        {
            // Vérifier si l'aliment est utilisé dans des repas
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM repas_aliments WHERE aliment_id = ?');
            $stmt->execute([$foodId]);
            $count = $stmt->fetchColumn();

            if ($count > 0)
            {
                return false; // Ne pas supprimer si utilisé
            }

            // Supprimer l'aliment
            $stmt = $this->db->prepare('DELETE FROM aliments WHERE id = ?');
            $stmt->execute([$foodId]);

            return true;
        } catch (\Exception $e)
        {
            error_log('Erreur suppression aliment: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Trouve un aliment par son ID.
     */
    public function findById(int $id): ?array
    {
        try
        {
            $stmt = $this->db->prepare('SELECT * FROM aliments WHERE id = ?');
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e)
        {
            error_log('Erreur lors de la recherche d\'aliment par ID: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Vérifie si un aliment existe par son barcode.
     */
    public function foodExistsByBarcode(string $barcode): bool
    {
        try
        {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM aliments WHERE openfoodfacts_id = ?');
            $stmt->execute([$barcode]);

            return (int)$stmt->fetchColumn() > 0;
        } catch (\Exception $e)
        {
            error_log('Erreur vérification barcode: ' . $e->getMessage());

            return false;
        }
    }
}
