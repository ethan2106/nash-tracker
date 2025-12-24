<?php

namespace App\Model;

use PDO;

/**
 * UserConfigModel - Gestion des configurations utilisateur personnalisables.
 */
class UserConfigModel
{
    private PDO $db;

    // Clés de configuration disponibles
    public const CONFIG_KEYS = [
        'activite_objectif_minutes' => [
            'default' => 30,
            'type' => 'int',
            'min' => 10,
            'max' => 180,
            'label' => 'Objectif activité (minutes/jour)',
        ],
        'notify_activity_enabled' => [
            'default' => 1,
            'type' => 'int',
            'min' => 0,
            'max' => 1,
            'label' => 'Notifications activité',
        ],
        'notify_goals_enabled' => [
            'default' => 1,
            'type' => 'int',
            'min' => 0,
            'max' => 1,
            'label' => 'Notifications objectifs atteints',
        ],
        'notify_quiet_start_hour' => [
            'default' => 22,
            'type' => 'int',
            'min' => 0,
            'max' => 23,
            'label' => 'Début période silencieuse (h)',
        ],
        'notify_quiet_end_hour' => [
            'default' => 7,
            'type' => 'int',
            'min' => 0,
            'max' => 23,
            'label' => 'Fin période silencieuse (h)',
        ],
        'lipides_max_g' => [
            'default' => 22,
            'type' => 'int',
            'min' => 10,
            'max' => 50,
            'label' => 'Maximum graisses saturées (g/jour)',
        ],
        'sucres_max_g' => [
            'default' => 50,
            'type' => 'int',
            'min' => 20,
            'max' => 100,
            'label' => 'Maximum sucres rapides (g/jour)',
        ],
        'imc_seuil_sous_poids' => [
            'default' => 18.5,
            'type' => 'float',
            'min' => 15.0,
            'max' => 20.0,
            'label' => 'Seuil IMC sous-poids',
        ],
        'imc_seuil_normal' => [
            'default' => 25.0,
            'type' => 'float',
            'min' => 20.0,
            'max' => 30.0,
            'label' => 'Seuil IMC normal',
        ],
        'imc_seuil_surpoids' => [
            'default' => 30.0,
            'type' => 'float',
            'min' => 25.0,
            'max' => 35.0,
            'label' => 'Seuil IMC surpoids',
        ],
        'medical_cardiac' => [
            'default' => 0,
            'type' => 'int',
            'min' => 0,
            'max' => 1,
            'label' => 'Problèmes cardiaques',
        ],
        'medical_diabetes' => [
            'default' => 0,
            'type' => 'int',
            'min' => 0,
            'max' => 1,
            'label' => 'Diabète',
        ],
        'medical_other' => [
            'default' => 0,
            'type' => 'int',
            'min' => 0,
            'max' => 1,
            'label' => 'Autres conditions médicales',
        ],
    ];

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Récupère une configuration utilisateur.
     */
    public function get(int $userId, string $key): mixed
    {
        if (!isset(self::CONFIG_KEYS[$key]))
        {
            throw new \InvalidArgumentException("Clé de configuration invalide: $key");
        }

        $stmt = $this->db->prepare('
            SELECT config_value FROM user_config
            WHERE user_id = ? AND config_key = ?
        ');
        $stmt->execute([$userId, $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result)
        {
            return $this->castValue($result['config_value'], self::CONFIG_KEYS[$key]['type']);
        }

        return self::CONFIG_KEYS[$key]['default'];
    }

    /**
     * Définit une configuration utilisateur.
     */
    public function set(int $userId, string $key, mixed $value): bool
    {
        if (!isset(self::CONFIG_KEYS[$key]))
        {
            throw new \InvalidArgumentException("Clé de configuration invalide: $key");
        }

        // Validation de la valeur
        $config = self::CONFIG_KEYS[$key];
        if ($value < $config['min'])
        {
            throw new \InvalidArgumentException("Valeur trop petite pour $key");
        }
        if ($value > $config['max'])
        {
            throw new \InvalidArgumentException("Valeur trop grande pour $key");
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare('
            INSERT OR REPLACE INTO user_config (user_id, config_key, config_value, updated_at)
            VALUES (?, ?, ?, ?)
        ');

        return $stmt->execute([$userId, $key, (string)$value, $now]);
    }

    /**
     * Récupère toutes les configurations d'un utilisateur.
     */
    public function getAll(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT config_key, config_value FROM user_config
            WHERE user_id = ?
        ');
        $stmt->execute([$userId]);
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $result = [];
        foreach (self::CONFIG_KEYS as $key => $config)
        {
            $result[$key] = isset($configs[$key])
                ? $this->castValue($configs[$key], $config['type'])
                : $config['default'];
        }

        return $result;
    }

    /**
     * Réinitialise une configuration à sa valeur par défaut.
     */
    public function reset(int $userId, string $key): bool
    {
        if (!isset(self::CONFIG_KEYS[$key]))
        {
            throw new \InvalidArgumentException("Clé de configuration invalide: $key");
        }

        $stmt = $this->db->prepare('
            DELETE FROM user_config
            WHERE user_id = ? AND config_key = ?
        ');

        return $stmt->execute([$userId, $key]);
    }

    /**
     * Cast une valeur string vers le bon type.
     */
    private function castValue(string $value, string $type): mixed
    {
        return match ($type)
        {
            'int' => (int)$value,
            'float' => (float)$value,
            'bool' => (bool)$value,
            default => $value,
        };
    }

    /**
     * Initialise les configurations par défaut pour un nouvel utilisateur.
     */
    public function initializeDefaults(int $userId): void
    {
        foreach (self::CONFIG_KEYS as $key => $config)
        {
            $this->set($userId, $key, $config['default']);
        }
    }
}
