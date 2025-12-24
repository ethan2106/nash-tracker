<?php

namespace App\Service;

use App\Model\HistoriqueMesuresModel;
use App\Model\ImcModel;
use App\Repository\ObjectifsRepositoryInterface;

/**
 * ImcSaveService - Gère la sauvegarde des données IMC.
 * Responsabilités :
 * - Validation des inputs
 * - Normalisation des données
 * - Sauvegarde ObjectifsModel + HistoriqueMesuresModel
 * - Invalidation des caches.
 */
class ImcSaveService
{
    public function __construct(
        private CacheService $cache,
        private ObjectifsRepositoryInterface $objectifsRepo,
        private HistoriqueMesuresModel $historiqueModel
    ) {
    }

    /**
     * Sauvegarde les objectifs et mesures IMC.
     */
    public function saveImcData(int $userId, array $request): bool
    {
        // Validation des inputs de base
        $validationErrors = ImcModel::validateInputs($request);
        if (!empty($validationErrors))
        {
            throw new \InvalidArgumentException('Erreurs de validation : ' . implode(', ', $validationErrors));
        }

        // Calculer toutes les valeurs (IMC, besoins caloriques, objectifs macros)
        $calculatedData = ImcModel::calculate($request);

        // Préparer les données pour la sauvegarde
        $data = [
            'calories_perte' => $calculatedData['calories_perte'],
            'sucres_max' => $calculatedData['sucres_max'],
            'glucides' => $calculatedData['glucides'],
            'graisses_sat_max' => $calculatedData['graisses_sat_max'],
            'graisses_insaturees' => $calculatedData['graisses_insaturees'],
            'proteines_min' => $calculatedData['proteines_min'],
            'proteines_max' => $calculatedData['proteines_max'],
            'fibres_min' => $calculatedData['fibres_min'],
            'fibres_max' => $calculatedData['fibres_max'],
            'sodium_max' => $calculatedData['sodium_max'],
            'taille' => $calculatedData['taille'],
            'poids' => $calculatedData['poids'],
            'annee' => $calculatedData['annee'],
            'sexe' => $calculatedData['sexe'],
            'activite' => $calculatedData['activite'],
            'imc' => $calculatedData['imc'],
            'objectif' => $calculatedData['objectif'],
        ];

        // Ajoute l'ID user
        $data['user_id'] = $userId;

        // Appel du model
        $success = $this->objectifsRepo->save($data);

        // Sauvegarder la mesure dans l'historique si succès
        if ($success)
        {
            // Invalider tous les caches affectés par le changement d'objectifs
            $this->cache->delete('imc', 'objectifs_' . $userId);
            $this->cache->clearNamespace('dashboard'); // Dashboard utilise les objectifs
            $this->cache->clearNamespace('profile'); // Profile utilise aussi les objectifs
            $this->historiqueModel->saveMesure(
                $userId,
                (float)$data['poids'],
                (float)$data['imc'],
                (float)$data['taille']
            );
        }

        return $success;
    }
}
