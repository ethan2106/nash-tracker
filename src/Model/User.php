<?php

namespace App\Model;

use Exception;

/**
 * User - Modèle pour la gestion des utilisateurs.
 * Responsabilités :
 * - Inscription d'utilisateurs (validation, hashage mot de passe, insertion DB)
 * - Authentification (vérification login/mot de passe)
 * - Interactions directes avec la base de données via Database.
 */
class User
{
    public function __construct(private \PDO $db)
    {
    }

    public function getDb(): \PDO
    {
        return $this->db;
    }

    public function register($data)
    {
        // Validation basique
        if (empty($data['pseudo']) || empty($data['email']) || empty($data['password']))
        {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
        if ($data['password'] !== $data['password_confirm'])
        {
            return ['success' => false, 'message' => 'Les mots de passe ne correspondent pas.'];
        }
        // Vérifier si l'email existe déjà
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $data['email']]);
        if ($stmt->fetch())
        {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
        }

        // Vérifier si le pseudo existe déjà
        $stmt = $this->db->prepare('SELECT id FROM users WHERE pseudo = :pseudo');
        $stmt->execute(['pseudo' => $data['pseudo']]);
        if ($stmt->fetch())
        {
            return ['success' => false, 'message' => 'Ce pseudo est déjà utilisé.'];
        }
        // Insérer l'utilisateur
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (pseudo, email, mot_de_passe, date_inscription) VALUES (:pseudo, :email, :mot_de_passe, datetime(\'now\'))');
        $ok = $stmt->execute([
            'pseudo' => $data['pseudo'],
            'email' => $data['email'],
            'mot_de_passe' => $hash,
        ]);
        if ($ok)
        {
            return ['success' => true, 'message' => 'Inscription réussie !'];
        } else
        {
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription.'];
        }
    }

    public function createUser($data)
    {
        // Validation basique
        if (empty($data['name']) || empty($data['email']) || empty($data['password']))
        {
            throw new Exception('Tous les champs sont obligatoires.');
        }

        // Vérifier si l'email existe déjà
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $data['email']]);
        if ($stmt->fetch())
        {
            throw new Exception('Email déjà utilisé.');
        }

        // Insérer l'utilisateur
        $stmt = $this->db->prepare('INSERT INTO users (pseudo, email, mot_de_passe, date_inscription) VALUES (:pseudo, :email, :mot_de_passe, datetime(\'now\'))');
        $stmt->execute([
            'pseudo' => $data['name'],
            'email' => $data['email'],
            'mot_de_passe' => $data['password'],
        ]);

        return $this->db->lastInsertId();
    }

    public function login($data)
    {
        if (empty($data['email']) || empty($data['password']))
        {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch();
        if ($user && password_verify($data['password'], $user['mot_de_passe']))
        {
            // Connexion OK, retourne juste les infos
            return [
                'success' => true,
                'message' => 'Connexion réussie !',
                'user' => [
                    'id' => $user['id'],
                    'pseudo' => $user['pseudo'],
                    'email' => $user['email'],
                    'date_inscription' => $user['date_inscription'] ?? null,
                ],
            ];
        } else
        {
            return ['success' => false, 'message' => 'Identifiants incorrects.'];
        }
    }
}
