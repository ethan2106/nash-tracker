-- ============================================================
-- WALKTRACK - Ajout des heures départ/arrivée (SQLite)
-- Migration: 003_walktrack_times.sqlite.sql
-- Date: 2025-12-21
-- ============================================================

-- Ajout des colonnes start_time et end_time à la table walks
ALTER TABLE walks
    ADD COLUMN start_time TIME DEFAULT NULL;
ALTER TABLE walks
    ADD COLUMN end_time TIME DEFAULT NULL;