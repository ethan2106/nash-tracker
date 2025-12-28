<?php

namespace App\Model;

use Exception;
use PDO;

/**
 * MealModel - Modèle pour la gestion des repas et aliments.
 * Responsabilités :
 * - CRUD des repas (ajout, modification, suppression)
 * - Gestion du catalogue alimentaire
 * - Calculs nutritionnels (calories, protéines, etc.)
 * - Interactions avec la table meals et aliments.
 */
class MealModel
{
    public function __construct(private \PDO $db)
    {
    }

    /**
     * Télécharger et sauvegarder une image depuis une URL.
     */
    public function downloadAndSaveImage($imageUrl, $code = null)
    {
        try
        {
            if (empty($imageUrl))
            {
                return null;
            }

            // Créer le dossier images/foods s'il n'existe pas
            $imagesDir = __DIR__ . '/../../public/images/foods/';
            if (!is_dir($imagesDir))
            {
                mkdir($imagesDir, 0755, true);
            }

            // Générer un nom de fichier unique
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension))
            {
                $extension = 'jpg';
            }

            $filename = ($code ? $code : uniqid('food_')) . '.' . $extension;
            $filepath = $imagesDir . $filename;

            // Télécharger l'image
            $imageData = file_get_contents($imageUrl);
            if ($imageData === false)
            {
                error_log("Erreur lors du téléchargement de l'image: $imageUrl");

                return null;
            }

            // Sauvegarder l'image
            if (file_put_contents($filepath, $imageData) === false)
            {
                error_log("Erreur lors de la sauvegarde de l'image: $filepath");

                return null;
            }

            // Retourner le chemin relatif pour le web
            return '/images/foods/' . $filename;
        } catch (Exception $e)
        {
            error_log("Erreur lors du téléchargement de l'image: " . $e->getMessage());

            return null;
        }
    }

    /**
     * Mettre à jour l'image d'un aliment depuis OpenFoodFacts.
     */
    public function updateFoodImage($foodId, $openfoodfactsId)
    {
        try
        {
            // Récupérer l'image_url depuis l'API
            $apiUrl = "https://world.openfoodfacts.org/api/v0/product/{$openfoodfactsId}.json";
            $json = file_get_contents($apiUrl);

            if ($json === false)
            {
                return false;
            }

            $data = json_decode($json, true);

            if ($data['status'] !== 1 || !isset($data['product']['image_url']))
            {
                return false;
            }

            $imageUrl = $data['product']['image_url'];

            // Télécharger et sauvegarder l'image
            $imagePath = $this->downloadAndSaveImage($imageUrl, $openfoodfactsId);

            if ($imagePath)
            {
                // Mettre à jour la base de données
                $stmt = $this->db->prepare('UPDATE aliments SET image_path = ? WHERE id = ?');
                $stmt->execute([$imagePath, $foodId]);

                return true;
            }

            return false;
        } catch (Exception $e)
        {
            error_log("Erreur lors de la mise à jour de l'image: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Ajouter un aliment depuis OpenFoodFacts à la base de données.
     */
    public function addFoodFromOpenFoodFacts($foodData)
    {
        try
        {
            // Télécharger et sauvegarder l'image si elle existe
            $imagePath = null;
            if (!empty($foodData['image_url']))
            {
                $imagePath = $this->downloadAndSaveImage($foodData['image_url'], $foodData['code'] ?? null);
            }

            // Vérifier si l'aliment existe déjà (par openfoodfacts_id)
            $existingFoodId = null;
            if (!empty($foodData['code']))
            {
                $checkStmt = $this->db->prepare('SELECT id FROM aliments WHERE openfoodfacts_id = ?');
                $checkStmt->execute([$foodData['code']]);
                $existingFoodId = $checkStmt->fetchColumn();
            }

            // Préparer les autres_infos en JSON
            $autresInfos = [
                'marque' => $foodData['brands'] ?? '',
                'image_url' => $foodData['image_url'] ?? '',
                'code_barre' => $foodData['code'] ?? '',
                'source' => 'openfoodfacts',
            ];

            if ($existingFoodId)
            {
                // Mettre à jour l'aliment existant
                $stmt = $this->db->prepare('
                    UPDATE aliments SET
                        nom = ?,
                        category_id = ?,
                        calories_100g = ?,
                        proteines_100g = ?,
                        glucides_100g = ?,
                        sucres_100g = ?,
                        lipides_100g = ?,
                        acides_gras_satures_100g = ?,
                        fibres_100g = ?,
                        sodium_100g = ?,
                        image_path = ?,
                        autres_infos = ?
                    WHERE id = ?
                ');

                $stmt->execute([
                    $foodData['product_name'] ?? '',
                    null, // category_id - à déterminer plus tard
                    $foodData['nutriments']['energy-kcal_100g'] ?? $foodData['nutriments']['energy-kcal'] ?? 0,
                    $foodData['nutriments']['proteins_100g'] ?? $foodData['nutriments']['proteins'] ?? 0,
                    $foodData['nutriments']['carbohydrates_100g'] ?? $foodData['nutriments']['carbohydrates'] ?? 0,
                    $foodData['nutriments']['sugars_100g'] ?? $foodData['nutriments']['sugars'] ?? 0,
                    $foodData['nutriments']['fat_100g'] ?? $foodData['nutriments']['fat'] ?? 0,
                    $foodData['nutriments']['saturated-fat_100g'] ?? $foodData['nutriments']['saturated-fat'] ?? 0,
                    $foodData['nutriments']['fiber_100g'] ?? $foodData['nutriments']['fiber'] ?? 0,
                    $foodData['nutriments']['sodium_100g'] ?? $foodData['nutriments']['sodium'] ?? 0,
                    $imagePath,
                    json_encode($autresInfos),
                    $existingFoodId,
                ]);

                return $existingFoodId;
            } else
            {
                // Insérer un nouvel aliment
                $stmt = $this->db->prepare('
                    INSERT INTO aliments (nom, category_id, calories_100g, proteines_100g, glucides_100g, sucres_100g, lipides_100g, acides_gras_satures_100g, fibres_100g, sodium_100g, openfoodfacts_id, image_path, autres_infos)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');

                $stmt->execute([
                    $foodData['product_name'] ?? '',
                    null, // category_id - à déterminer plus tard
                    $foodData['nutriments']['energy-kcal_100g'] ?? $foodData['nutriments']['energy-kcal'] ?? 0,
                    $foodData['nutriments']['proteins_100g'] ?? $foodData['nutriments']['proteins'] ?? 0,
                    $foodData['nutriments']['carbohydrates_100g'] ?? $foodData['nutriments']['carbohydrates'] ?? 0,
                    $foodData['nutriments']['sugars_100g'] ?? $foodData['nutriments']['sugars'] ?? 0,
                    $foodData['nutriments']['fat_100g'] ?? $foodData['nutriments']['fat'] ?? 0,
                    $foodData['nutriments']['saturated-fat_100g'] ?? $foodData['nutriments']['saturated-fat'] ?? 0,
                    $foodData['nutriments']['fiber_100g'] ?? $foodData['nutriments']['fiber'] ?? 0,
                    $foodData['nutriments']['sodium_100g'] ?? $foodData['nutriments']['sodium'] ?? 0,
                    $foodData['code'] ?? '',
                    $imagePath,
                    json_encode($autresInfos),
                ]);

                return $this->db->lastInsertId();
            }
        } catch (Exception $e)
        {
            error_log("Erreur lors de l'ajout d'aliment OpenFoodFacts: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Ajouter un aliment manuellement à la base de données.
     */
    public function addFoodManually($foodData)
    {
        try
        {
            $stmt = $this->db->prepare('
                INSERT INTO aliments (nom, category_id, calories_100g, proteines_100g, glucides_100g, sucres_100g, lipides_100g, acides_gras_satures_100g, fibres_100g, sodium_100g, autres_infos)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            // Préparer les autres_infos en JSON
            $autresInfos = [
                'marque' => $foodData['marque'] ?? '',
                'categorie' => $foodData['categorie'] ?? '',
                'source' => 'manuel',
            ];

            $stmt->execute([
                $foodData['nom'] ?? '',
                null, // category_id - à déterminer plus tard
                $foodData['calories_100g'] ?? 0,
                $foodData['proteines_100g'] ?? 0,
                $foodData['glucides_100g'] ?? 0,
                $foodData['sucres_100g'] ?? 0,
                $foodData['lipides_100g'] ?? 0,
                $foodData['acides_gras_satures_100g'] ?? 0,
                $foodData['fibres_100g'] ?? 0,
                $foodData['sodium_100g'] ?? 0,
                json_encode($autresInfos),
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e)
        {
            error_log("Erreur lors de l'ajout d'aliment manuel: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Ajouter un aliment existant à un repas.
     */
    public function addFoodToMeal($mealId, $foodId, $quantity)
    {
        try
        {
            // Vérifier si l'aliment existe
            $stmt = $this->db->prepare('SELECT id FROM aliments WHERE id = ?');
            $stmt->execute([$foodId]);
            if (!$stmt->fetch())
            {
                return false;
            }

            // Ajouter à repas_aliments
            $stmt = $this->db->prepare('
                INSERT INTO repas_aliments (repas_id, aliment_id, quantite_g)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([$mealId, $foodId, $quantity]);

            // If the meal is for today, bump its timestamp to now so it appears at the top of recent activity
            try
            {
                $today = date('Y-m-d');
                $update = $this->db->prepare('UPDATE repas SET date_heure = datetime(\'now\') WHERE id = ? AND date_heure BETWEEN ? AND ?');
                $update->execute([$mealId, $today . ' 00:00:00', $today . ' 23:59:59']);
            } catch (Exception $e)
            {
                // Non critical; ignore but log
                error_log('Erreur mise à jour date_heure repas: ' . $e->getMessage());
            }

            return true;
        } catch (Exception $e)
        {
            error_log("Erreur lors de l'ajout d'aliment au repas: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Créer un repas et ajouter un aliment en une seule transaction atomique.
     */
    public function createMealWithFood($userId, $mealType, $foodId, $quantity, $dateTime = null)
    {
        try
        {
            $this->db->beginTransaction();

            // Créer le repas
            $mealId = $this->createMeal($userId, $mealType, $dateTime);
            if (!$mealId)
            {
                $this->db->rollBack();

                return false;
            }

            // Ajouter l'aliment (cette méthode ne lance pas d'exception, elle retourne false en cas d'erreur)
            $result = $this->addFoodToMeal($mealId, $foodId, $quantity);
            if (!$result)
            {
                $this->db->rollBack();

                return false;
            }

            $this->db->commit();

            return $mealId;

        } catch (Exception $e)
        {
            $this->db->rollBack();
            error_log('Erreur lors de la création du repas avec aliment: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Créer un nouveau repas.
     */
    public function createMeal($userId, $mealType = 'repas', $dateTime = null)
    {
        try
        {
            $dateTime = $dateTime ?? date('Y-m-d H:i:s');

            $stmt = $this->db->prepare('
                INSERT INTO repas (user_id, type_repas, date_heure)
                VALUES (?, ?, ?)
            ');
            $result = $stmt->execute([$userId, $mealType, $dateTime]);

            if ($result)
            {
                $lastId = $this->db->lastInsertId();

                return $lastId;
            } else
            {
                return false;
            }
        } catch (Exception $e)
        {
            error_log('Erreur lors de la création du repas: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return false;
        }
    }

    /**
     * Récupérer un repas par date et type.
     */
    public function getMealByDateAndType($userId, $date, $mealType)
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT * FROM repas
                WHERE user_id = ? AND date_heure BETWEEN ? AND ? AND type_repas = ?
                ORDER BY date_heure DESC
                LIMIT 1
            ');
            $stmt->execute([$userId, $date . ' 00:00:00', $date . ' 23:59:59', $mealType]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e)
        {
            error_log('Erreur lors de la récupération du repas: ' . $e->getMessage());

            return false;
        }
    }

    public function getMealsByDate($userId, $date)
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT r.*, COUNT(ra.id) AS aliment_count,
                       SUM((a.calories_100g * ra.quantite_g) / 100) AS calories_total,
                       SUM((a.proteines_100g * ra.quantite_g) / 100) AS proteines_total,
                       SUM((a.glucides_100g * ra.quantite_g) / 100) AS glucides_total,
                       SUM((a.lipides_100g * ra.quantite_g) / 100) AS lipides_total,
                       SUM((a.acides_gras_satures_100g * ra.quantite_g) / 100) AS graisses_sat_total,
                       SUM((a.sucres_100g * ra.quantite_g) / 100) AS sucres_total,
                       SUM((a.fibres_100g * ra.quantite_g) / 100) AS fibres_total,
                       GROUP_CONCAT(ra.aliment_id) as aliment_ids,
                       GROUP_CONCAT(ra.quantite_g) as quantites
                FROM repas r
                LEFT JOIN repas_aliments ra ON r.id = ra.repas_id
                LEFT JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ? AND DATE(r.date_heure) = ?
                GROUP BY r.id
                ORDER BY r.date_heure
            ');
            $stmt->execute([$userId, $date]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e)
        {
            error_log('Erreur lors de la récupération des repas: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Récupérer les aliments d'un repas avec leurs détails.
     */
    public function getAlimentsForRepas($repasId)
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT a.*, ra.quantite_g as quantite_g
                FROM repas_aliments ra
                JOIN aliments a ON a.id = ra.aliment_id
                WHERE ra.repas_id = ?
            ');
            $stmt->execute([$repasId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e)
        {
            error_log('Erreur lors de la récupération des aliments du repas: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Récupérer les détails d'un repas avec ses aliments.
     */
    public function getMealDetails($mealId)
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT r.*, ra.quantite_g, a.nom, a.calories_100g, a.proteines_100g,
                       a.glucides_100g, a.lipides_100g, a.fibres_100g, a.autres_infos
                FROM repas r
                JOIN repas_aliments ra ON r.id = ra.repas_id
                JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.id = ?
                ORDER BY a.nom
            ');
            $stmt->execute([$mealId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e)
        {
            error_log('Erreur lors de la récupération des détails du repas: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Supprimer un aliment d'un repas.
     */
    public function removeFoodFromMeal($mealId, $foodId)
    {
        try
        {
            $stmt = $this->db->prepare('
                DELETE FROM repas_aliments
                WHERE repas_id = ? AND aliment_id = ?
            ');
            $stmt->execute([$mealId, $foodId]);

            // Vérifier si une ligne a été effectivement supprimée
            return $stmt->rowCount() > 0;
        } catch (Exception $e)
        {
            error_log("Erreur lors de la suppression d'aliment du repas: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Supprimer un aliment.
     */
    public function deleteFood($alimentId)
    {
        try
        {
            // Vérifier si l'aliment est utilisé dans des repas
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM repas_aliments WHERE aliment_id = ?');
            $stmt->execute([$alimentId]);
            $count = $stmt->fetchColumn();

            if ($count > 0)
            {
                // L'aliment est utilisé dans des repas, ne pas le supprimer
                return ['success' => false, 'message' => 'Cet aliment ne peut pas être supprimé car il est utilisé dans ' . $count . ' repas(s).'];
            }

            // Supprimer l'aliment
            $stmt = $this->db->prepare('DELETE FROM aliments WHERE id = ?');
            $stmt->execute([$alimentId]);

            return ['success' => true, 'message' => 'Aliment supprimé avec succès'];
        } catch (Exception $e)
        {
            error_log('Erreur suppression aliment: ' . $e->getMessage());

            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        }
    }

    /**
     * Mettre à jour le nom d'un aliment.
     */
    public function updateFoodName($foodId, $newName)
    {
        try
        {
            $stmt = $this->db->prepare('UPDATE aliments SET nom = ? WHERE id = ?');
            $stmt->execute([$newName, $foodId]);

            return true;
        } catch (Exception $e)
        {
            error_log('Erreur lors de la mise à jour du nom d\'aliment: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Supprimer un repas et ses aliments associés.
     */
    public function deleteMeal($repasId)
    {
        try
        {
            // Vérifier que l'utilisateur est connecté
            if (!isset($_SESSION['user']['id']))
            {
                return false;
            }
            $userId = $_SESSION['user']['id'];

            // Vérifier que le repas appartient à l'utilisateur
            $stmt = $this->db->prepare('SELECT id FROM repas WHERE id = ? AND user_id = ?');
            $stmt->execute([$repasId, $userId]);
            if (!$stmt->fetch())
            {
                return false; // Repas non trouvé ou pas à l'utilisateur
            }

            // Supprimer d'abord les aliments du repas
            $stmt = $this->db->prepare('DELETE FROM repas_aliments WHERE repas_id = ?');
            $stmt->execute([$repasId]);

            // Puis supprimer le repas
            $stmt = $this->db->prepare('DELETE FROM repas WHERE id = ? AND user_id = ?');
            $stmt->execute([$repasId, $userId]);

            return true;
        } catch (Exception $e)
        {
            error_log('Erreur suppression repas: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Rechercher des aliments dans la base de données.
     */
    public function searchFoods($query, $limit = 20)
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT * FROM aliments
                WHERE LOWER(nom) LIKE LOWER(?) OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(autres_infos, "$.marque"))) LIKE LOWER(?)
                ORDER BY nom
                LIMIT ' . (int)$limit . '
            ');
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e)
        {
            error_log("Erreur lors de la recherche d'aliments: " . $e->getMessage());

            return [];
        }
    }

    /**
     * Récupérer un aliment par son ID.
     */
    public function getFoodById($foodId)
    {
        try
        {
            $stmt = $this->db->prepare('SELECT * FROM aliments WHERE id = ?');
            $stmt->execute([$foodId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e)
        {
            error_log("Erreur lors de la récupération de l'aliment: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Récupérer tous les aliments sauvegardés avec pagination.
     */
    public function getSavedFoods($limit = null, $offset = 0)
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
        } catch (Exception $e)
        {
            error_log('Erreur lors de la récupération des aliments sauvegardés: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Compter le nombre total d'aliments sauvegardés.
     */
    public function countSavedFoods()
    {
        try
        {
            $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM aliments');
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur lors du comptage des aliments sauvegardés: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Rechercher dans les aliments sauvegardés.
     */
    public function searchSavedFoods($query)
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT * FROM aliments
                WHERE LOWER(nom) LIKE LOWER(?) OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(autres_infos, "$.marque"))) LIKE LOWER(?)
                ORDER BY nom ASC
                LIMIT 20
            ');
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e)
        {
            error_log("Erreur lors de la recherche d'aliments sauvegardés: " . $e->getMessage());

            return [];
        }
    }
}
