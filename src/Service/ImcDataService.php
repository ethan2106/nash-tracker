<?php

namespace App\Service;

use App\Model\ImcModel;
use App\Model\ObjectifsModel;

/**
 * ImcDataService - Gère la récupération et le calcul des données IMC.
 * Responsabilités :
 * - Récupération des objectifs avec cache
 * - Calculs IMC, BMR, TDEE, etc.
 * - Retourne les données pour affichage.
 */
class ImcDataService
{
    private CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Récupère les données IMC pour affichage (avec cache).
     */
    public function getImcData(?int $userId, array $request = []): array
    {
        $saved = null;
        if ($userId)
        {
            $namespace = 'imc';
            $key = 'objectifs_' . $userId;

            $cached = $this->cache->get($namespace, $key);
            if ($cached !== null)
            {
                $saved = $cached;
            } else
            {
                $saved = ObjectifsModel::getByUser($userId);
                $this->cache->set($namespace, $key, $saved, CacheService::TTL_MEDIUM);
            }
        }

        // Si POST, on calcule avec les données soumises (avant sauvegarde)
        if (!empty($request))
        {
            $data = ImcModel::calculate($request);
            if ($saved)
            {
                $data = array_merge($saved, $data);
            }
        } else
        {
            // Si données en base, on les passe au calculateur pour générer tous les champs nécessaires à la vue
            if ($saved)
            {
                $data = ImcModel::calculate($saved);
                // On fusionne pour garder les valeurs brutes et calculées
                $data = array_merge($saved, $data);
            } else
            {
                $data = ImcModel::calculate([]);
            }
        }

        return $data;
    }
}
