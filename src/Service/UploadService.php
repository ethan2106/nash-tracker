<?php

namespace App\Service;

/**
 * Service pour gérer l'upload et la gestion des images
 * Responsabilités :
 * - Validation des fichiers uploadés
 * - Gestion du stockage des images
 * - Génération des noms de fichiers uniques.
 */
class UploadService
{
    /**
     * Gérer l'upload d'une image pour un aliment.
     */
    public function handleImageUpload(array $file, string $subDir = 'foods'): array
    {
        // Vérifier le type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes))
        {
            return ['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.'];
        }

        // Vérifier la taille (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize)
        {
            return ['error' => 'Fichier trop volumineux. Taille maximum : 2MB.'];
        }

        // Créer le dossier s'il n'existe pas
        $uploadDir = __DIR__ . '/../../public/images/' . $subDir . '/';
        if (!is_dir($uploadDir))
        {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom unique pour le fichier
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('food_', true) . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Déplacer le fichier uploadé
        if (move_uploaded_file($file['tmp_name'], $filepath))
        {
            return ['url' => '/images/' . $subDir . '/' . $filename];
        } else
        {
            return ['error' => 'Erreur lors de l\'upload du fichier.'];
        }
    }

    /**
     * Supprimer une image.
     */
    public function deleteImage(string $imageUrl): bool
    {
        if (empty($imageUrl))
        {
            return true;
        }

        // Convertir l'URL relative en chemin absolu
        $filepath = __DIR__ . '/../../public' . $imageUrl;

        if (file_exists($filepath))
        {
            return unlink($filepath);
        }

        return true; // Fichier déjà supprimé ou n'existe pas
    }
}
