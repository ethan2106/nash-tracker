-- Migration: Suppression complète de l'hydratation
-- Date: 
-- Description: Supprime la table consommation_eau et ses références

DROP TABLE IF EXISTS consommation_eau;

-- Supprimer l'index s'il existe
DROP INDEX IF EXISTS idx_consommation_eau_user_date;
DROP INDEX IF EXISTS idx_eau_user_date;
