<?php

namespace App\Model;

use PDO;
use PDOException;

/**
 * Modèle User - Gestion des comptes utilisateurs
 * Méthodes CRUD pour email, pseudo, mot de passe, suppression compte.
 */
class UserModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Récupère un utilisateur par son ID.
     *
     * @param int $userId ID de l'utilisateur
     * @return array|null Données utilisateur ou null
     */
    public function getById($userId): ?array
    {
        try
        {
            $sql = 'SELECT id, pseudo, email, date_inscription FROM users WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $user ?: null;
        } catch (PDOException $e)
        {
            error_log('Erreur getById UserModel: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Vérifie si un email existe déjà (pour éviter doublons).
     *
     * @param string $email Email à vérifier
     * @param int|null $excludeUserId ID utilisateur à exclure (pour update)
     * @return bool True si email existe, False sinon
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        try
        {
            if ($excludeUserId)
            {
                $sql = 'SELECT COUNT(*) FROM users WHERE email = ? AND id != ?';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$email, $excludeUserId]);
            } else
            {
                $sql = 'SELECT COUNT(*) FROM users WHERE email = ?';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$email]);
            }

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e)
        {
            error_log('Erreur emailExists UserModel: ' . $e->getMessage());

            return true; // Par sécurité, considérer comme existant en cas d'erreur
        }
    }

    /**
     * Vérifie si un pseudo existe déjà.
     *
     * @param string $pseudo Pseudo à vérifier
     * @param int|null $excludeUserId ID utilisateur à exclure (pour update)
     * @return bool True si pseudo existe, False sinon
     */
    public function pseudoExists(string $pseudo, ?int $excludeUserId = null): bool
    {
        try
        {
            if ($excludeUserId)
            {
                $sql = 'SELECT COUNT(*) FROM users WHERE pseudo = ? AND id != ?';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$pseudo, $excludeUserId]);
            } else
            {
                $sql = 'SELECT COUNT(*) FROM users WHERE pseudo = ?';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$pseudo]);
            }

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e)
        {
            error_log('Erreur pseudoExists UserModel: ' . $e->getMessage());

            return true;
        }
    }

    /**
     * Met à jour l'email d'un utilisateur.
     *
     * @param int $userId ID utilisateur
     * @param string $newEmail Nouvel email
     * @return bool True si succès, False sinon
     */
    public function updateEmail(int $userId, string $newEmail): bool
    {
        // Validation email
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL))
        {
            error_log('Email invalide : ' . $newEmail);

            return false;
        }

        // Vérifier si email déjà utilisé
        if ($this->emailExists($newEmail, $userId))
        {
            error_log('Email déjà utilisé : ' . $newEmail);

            return false;
        }

        try
        {
            $sql = 'UPDATE users SET email = ? WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([$newEmail, $userId]);
        } catch (PDOException $e)
        {
            error_log('Erreur updateEmail UserModel: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Met à jour le pseudo d'un utilisateur.
     *
     * @param int $userId ID utilisateur
     * @param string $newPseudo Nouveau pseudo
     * @return bool True si succès, False sinon
     */
    public function updatePseudo(int $userId, string $newPseudo): bool
    {
        // Validation pseudo (3-50 caractères, alphanumérique + _ -)
        if (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $newPseudo))
        {
            error_log('Pseudo invalide : ' . $newPseudo);

            return false;
        }

        // Vérifier si pseudo déjà utilisé
        if ($this->pseudoExists($newPseudo, $userId))
        {
            error_log('Pseudo déjà utilisé : ' . $newPseudo);

            return false;
        }

        try
        {
            $sql = 'UPDATE users SET pseudo = ? WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([$newPseudo, $userId]);
        } catch (PDOException $e)
        {
            error_log('Erreur updatePseudo UserModel: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Met à jour le mot de passe d'un utilisateur.
     *
     * @param int $userId ID utilisateur
     * @param string $currentPassword Mot de passe actuel (pour vérification)
     * @param string $newPassword Nouveau mot de passe
     * @return bool True si succès, False sinon
     */
    public function updatePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        // Récupérer le hash actuel
        try
        {
            $sql = 'SELECT mot_de_passe FROM users WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user)
            {
                error_log('Utilisateur non trouvé : ' . $userId);

                return false;
            }

            // Vérifier mot de passe actuel
            if (!password_verify($currentPassword, $user['mot_de_passe']))
            {
                error_log('Mot de passe actuel incorrect pour user : ' . $userId);

                return false;
            }

            // Validation nouveau mot de passe (min 8 caractères)
            if (strlen($newPassword) < 8)
            {
                error_log('Nouveau mot de passe trop court');

                return false;
            }

            // Hasher le nouveau mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Mettre à jour
            $sql = 'UPDATE users SET mot_de_passe = ? WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([$hashedPassword, $userId]);
        } catch (PDOException $e)
        {
            error_log('Erreur updatePassword UserModel: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Supprime un compte utilisateur et toutes ses données associées.
     *
     * @param int $userId ID utilisateur
     * @param string $password Mot de passe pour confirmation
     * @return bool True si succès, False sinon
     */
    public function deleteAccount(int $userId, string $password): bool
    {
        try
        {
            // Vérifier mot de passe
            $sql = 'SELECT mot_de_passe FROM users WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['mot_de_passe']))
            {
                error_log('Mot de passe incorrect pour suppression compte : ' . $userId);

                return false;
            }

            // Transaction pour garantir la suppression complète
            $this->pdo->beginTransaction();

            // Les suppressions CASCADE s'occuperont de :
            // - repas (FK user_id)
            // Mais certaines tables n'ont pas de FK, les supprimer manuellement :

            // Supprimer objectifs_nutrition
            $sql = 'DELETE FROM objectifs_nutrition WHERE user_id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);

            // Supprimer activités physiques
            $sql = 'DELETE FROM activites_physiques WHERE user_id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);

            // Supprimer l'utilisateur (CASCADE s'occupera du reste)
            $sql = 'DELETE FROM users WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);

            $this->pdo->commit();

            return true;
        } catch (PDOException $e)
        {
            $this->pdo->rollBack();
            error_log('Erreur deleteAccount UserModel: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Récupère les statistiques d'un utilisateur.
     *
     * @param int $userId ID utilisateur
     * @return array Statistiques (date inscription, nb repas, etc.)
     */
    public function getUserStats(int $userId): array
    {
        try
        {
            $stats = [];

            // Date d'inscription
            $sql = 'SELECT strftime("%d/%m/%Y", date_inscription) as date_inscription 
                    FROM users WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['date_inscription'] = $user['date_inscription'] ?? 'N/A';

            // Nombre de repas enregistrés
            $sql = 'SELECT COUNT(*) FROM repas WHERE user_id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $stats['nb_repas'] = $stmt->fetchColumn();

            // Nombre d'objectifs définis (historique)
            $sql = 'SELECT COUNT(*) FROM objectifs_nutrition WHERE user_id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $stats['nb_objectifs'] = $stmt->fetchColumn();

            return $stats;
        } catch (PDOException $e)
        {
            error_log('Erreur getUserStats UserModel: ' . $e->getMessage());

            return [];
        }
    }
}
