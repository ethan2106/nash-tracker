-- Migration: Ajout des index pour optimisation des requêtes (SQLite)
-- Date: 2025-12-21
-- Description: Index sur les colonnes user_id + date_heure pour les requêtes fréquentes

-- ============================================================
-- Index pour la table activites_physiques
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_activites_user_date ON activites_physiques(user_id, date_heure);

-- ============================================================
-- Index pour la table repas
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_repas_user_date ON repas(user_id, date_heure);
CREATE INDEX IF NOT EXISTS idx_repas_user_type_date ON repas(user_id, type_repas, date_heure);

-- ============================================================
-- Index pour la table repas_aliments (jointures fréquentes)
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_repas_aliments_repas ON repas_aliments(repas_id);
CREATE INDEX IF NOT EXISTS idx_repas_aliments_aliment ON repas_aliments(aliment_id);

-- ============================================================
-- Index pour la table prises_medicaments
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_prises_medicament_date ON prises_medicaments(medicament_id, date);

-- ============================================================
-- Index pour la table objectifs_nutrition
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_objectifs_user_actif ON objectifs_nutrition(user_id, actif);

-- ============================================================
-- Index pour la table historique_mesures
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_historique_user_date ON historique_mesures(user_id, date_mesure);

-- ============================================================
-- Index pour la table user_config
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_userconfig_user_key ON user_config(user_id, config_key);