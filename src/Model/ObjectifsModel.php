<?php

namespace App\Model;

class ObjectifsModel
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * Sauvegarde un nouvel objectif nutritionnel avec système de versioning.
     * Désactive automatiquement l'ancien objectif actif et crée une nouvelle version.
     *
     * @param array $data Données de l'objectif (calories, macros, infos utilisateur)
     * @return bool True si succès, False sinon
     */
    public function save(array $data, int $userId): bool
    {
        $fields = [
            'calories_perte', 'sucres_max', 'glucides', 'graisses_sat_max', 'graisses_insaturees',
            'proteines_min', 'proteines_max', 'fibres_min', 'fibres_max', 'sodium_max',
            'taille', 'poids', 'annee', 'sexe', 'activite', 'imc', 'objectif',
        ];

        foreach ($fields as $key)
        {
            if (!isset($data[$key]))
            {
                error_log("ObjectifsModel::save: Champ manquant: $key");

                return false;
            }
            if (in_array($key, ['calories_perte', 'sucres_max', 'graisses_sat_max', 'proteines_min', 'proteines_max', 'fibres_min', 'fibres_max', 'sodium_max', 'taille', 'poids', 'imc']) && (!is_numeric($data[$key]) || $data[$key] < 0))
            {
                error_log("ObjectifsModel::save: Valeur invalide pour $key: " . $data[$key]);

                return false;
            }
            if ($key === 'annee' && (!is_numeric($data[$key]) || $data[$key] < 1900))
            {
                error_log('ObjectifsModel::save: Année invalide: ' . $data[$key]);

                return false;
            }
            if ($key === 'sexe' && !in_array($data[$key], ['homme', 'femme']))
            {
                error_log('ObjectifsModel::save: Sexe invalide: ' . $data[$key]);

                return false;
            }
            if ($key === 'activite' && !in_array($data[$key], ['sedentaire', 'leger', 'modere', 'intense']))
            {
                error_log('ObjectifsModel::save: Activité invalide: ' . $data[$key]);

                return false;
            }
        }

        $nowDate = date('Y-m-d');

        try
        {
            // Début de transaction pour garantir la cohérence des données
            $this->pdo->beginTransaction();

            // Étape 1 : Désactiver l'ancien objectif actif (s'il existe)
            $deactivateSql = 'UPDATE objectifs_nutrition 
                             SET actif = 0, date_fin = ? 
                             WHERE user_id = ? AND actif = 1';
            $deactivateStmt = $this->pdo->prepare($deactivateSql);
            $deactivateStmt->execute([$nowDate, $userId]);

            // Étape 2 : Insérer le nouvel objectif actif
            $sql = 'INSERT INTO objectifs_nutrition 
                    (calories_perte, sucres_max, glucides, graisses_sat_max, graisses_insaturees, 
                     proteines_min, proteines_max, fibres_min, fibres_max, sodium_max, 
                     taille, poids, annee, sexe, activite, imc, objectif, user_id, 
                     actif, date_debut)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['calories_perte'],
                $data['sucres_max'],
                $data['glucides'],
                $data['graisses_sat_max'],
                $data['graisses_insaturees'],
                $data['proteines_min'],
                $data['proteines_max'],
                $data['fibres_min'],
                $data['fibres_max'],
                $data['sodium_max'],
                $data['taille'],
                $data['poids'],
                $data['annee'],
                $data['sexe'],
                $data['activite'],
                $data['imc'],
                $data['objectif'],
                $userId,
                $nowDate,
            ]);

            // Valider la transaction
            $this->pdo->commit();

            return true;
        } catch (\PDOException $e)
        {
            // Annuler la transaction en cas d'erreur
            if ($this->pdo->inTransaction())
            {
                $this->pdo->rollBack();
            }
            error_log('Erreur SQL dans ObjectifsModel::save: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Récupère l'objectif nutritionnel actif pour un utilisateur.
     *
     * @param int $user_id ID de l'utilisateur
     * @return array|null Données de l'objectif actif ou null si aucun
     */
    public function getByUser($user_id)
    {
        try
        {
            // Récupère uniquement l'objectif actif
            $sql = 'SELECT calories_perte, sucres_max, glucides, graisses_sat_max, graisses_insaturees, 
                           proteines_min, proteines_max, fibres_min, fibres_max, sodium_max, 
                           taille, poids, annee, sexe, activite, imc, objectif, 
                           date_debut, date_fin, actif 
                    FROM objectifs_nutrition 
                    WHERE user_id = ? AND actif = 1 
                    LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id]);

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e)
        {
            error_log('Erreur SQL dans ObjectifsModel::getByUser: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Récupère l'historique complet des objectifs nutritionnels pour un utilisateur.
     *
     * @param int $user_id ID de l'utilisateur
     * @return array Liste des objectifs triés par date (plus récent en premier)
     */
    public function getHistoriqueByUser($user_id): array
    {
        try
        {
            $sql = 'SELECT id, calories_perte, sucres_max, glucides, graisses_sat_max, graisses_insaturees, 
                           proteines_min, proteines_max, fibres_min, fibres_max, sodium_max, 
                           taille, poids, annee, sexe, activite, imc, objectif, 
                           date_debut, date_fin, actif, created_at 
                    FROM objectifs_nutrition 
                    WHERE user_id = ? 
                    ORDER BY date_debut DESC';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e)
        {
            error_log('Erreur SQL dans ObjectifsModel::getHistoriqueByUser: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Récupère l'objectif valide à une date donnée (pour analyses historiques).
     *
     * @param int $user_id ID de l'utilisateur
     * @param string $date Date au format Y-m-d
     * @return array|null Objectif valide à cette date ou null
     */
    public function getByUserAtDate($user_id, $date)
    {
        try
        {
            $sql = 'SELECT calories_perte, sucres_max, glucides, graisses_sat_max, graisses_insaturees, 
                           proteines_min, proteines_max, fibres_min, fibres_max, sodium_max, 
                           taille, poids, annee, sexe, activite, imc, objectif 
                    FROM objectifs_nutrition 
                    WHERE user_id = ? 
                      AND date_debut <= ? 
                      AND (date_fin IS NULL OR date_fin >= ?)
                    ORDER BY date_debut DESC 
                    LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $date, $date]);

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e)
        {
            error_log('Erreur SQL dans ObjectifsModel::getByUserAtDate: ' . $e->getMessage());

            return null;
        }
    }
}
