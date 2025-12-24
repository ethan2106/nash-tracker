-- ============================================================
-- WALKTRACK - Module de suivi des marches (SQLite)
-- Migration: 002_walktrack.sqlite.sql
-- Date: 2025-12-21
-- ============================================================

-- Table des marches enregistrées
CREATE TABLE IF NOT EXISTS walks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    walk_type TEXT NOT NULL DEFAULT 'marche' CHECK(walk_type IN ('marche', 'marche_rapide')),
    distance_km REAL NOT NULL,
    duration_minutes INTEGER NOT NULL,
    calories_burned INTEGER DEFAULT NULL,
    route_points TEXT DEFAULT NULL, -- JSON array de {lat, lng}
    note TEXT DEFAULT NULL,
    walk_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Index pour walks
CREATE INDEX IF NOT EXISTS idx_walks_user_date ON walks(user_id, walk_date);
CREATE INDEX IF NOT EXISTS idx_walks_date ON walks(walk_date);

-- Table des objectifs personnalisés par utilisateur
CREATE TABLE IF NOT EXISTS walk_objectives (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    km_per_day REAL NOT NULL DEFAULT 5.00,
    days_per_week INTEGER NOT NULL DEFAULT 4,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des parcours favoris
CREATE TABLE IF NOT EXISTS walk_routes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    distance_km REAL NOT NULL,
    route_points TEXT NOT NULL, -- JSON array de {lat, lng}
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Index pour walk_routes
CREATE INDEX IF NOT EXISTS idx_routes_user ON walk_routes(user_id);