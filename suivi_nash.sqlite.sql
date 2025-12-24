-- SQLite Schema for Nash-Tracker
-- Converted from MySQL dump
-- Date: 2025-12-21

PRAGMA foreign_keys = ON;
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT 1
);

-- Table: profiles
CREATE TABLE profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    date_naissance DATE,
    sexe VARCHAR(10),
    taille DECIMAL(5,2),
    poids_actuel DECIMAL(5,2),
    objectif_poids DECIMAL(5,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: user_config
CREATE TABLE user_config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    config_key VARCHAR(100) NOT NULL,
    config_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id, config_key)
);

-- Table: categories
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE
);

-- Table: aliments
CREATE TABLE aliments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(255) NOT NULL,
    category_id INTEGER,
    calories_100g REAL,
    proteines_100g REAL,
    glucides_100g REAL,
    sucres_100g REAL,
    lipides_100g REAL,
    acides_gras_satures_100g REAL,
    fibres_100g REAL,
    sodium_100g REAL,
    nutriscore VARCHAR(5),
    openfoodfacts_id VARCHAR(50),
    image_path VARCHAR(255),
    autres_infos TEXT, -- JSON
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Table: repas
CREATE TABLE repas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type_repas VARCHAR(20) NOT NULL CHECK(type_repas IN ('petit_dejeuner', 'dejeuner', 'diner', 'collation')),
    date_heure DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_calories REAL DEFAULT 0,
    total_proteines REAL DEFAULT 0,
    total_glucides REAL DEFAULT 0,
    total_lipides REAL DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: repas_aliments
CREATE TABLE repas_aliments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    repas_id INTEGER NOT NULL,
    aliment_id INTEGER NOT NULL,
    quantite_g REAL NOT NULL,
    calories REAL,
    proteines REAL,
    glucides REAL,
    lipides REAL,
    FOREIGN KEY (repas_id) REFERENCES repas(id) ON DELETE CASCADE,
    FOREIGN KEY (aliment_id) REFERENCES aliments(id) ON DELETE CASCADE
);

-- Table: activites_physiques
CREATE TABLE activites_physiques (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type_activite VARCHAR(50) NOT NULL,
    duree_minutes INTEGER NOT NULL,
    calories_depensees INTEGER NOT NULL,
    date_heure DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: medicaments
CREATE TABLE medicaments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(255) NOT NULL,
    dose VARCHAR(100),
    type VARCHAR(20) DEFAULT 'regulier' CHECK(type IN ('regulier', 'ponctuel')),
    frequence VARCHAR(100),
    heures_prise TEXT, -- JSON
    actif BOOLEAN DEFAULT 1,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: prises_medicaments
CREATE TABLE prises_medicaments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    medicament_id INTEGER NOT NULL,
    date DATE NOT NULL,
    periode VARCHAR(10) NOT NULL CHECK(periode IN ('matin', 'midi', 'soir', 'nuit')),
    status VARCHAR(4) NOT NULL DEFAULT 'non' CHECK(status IN ('pris', 'non')),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE CASCADE
);

-- Table: objectifs_nutrition
CREATE TABLE objectifs_nutrition (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    calories_perte REAL NOT NULL,
    sucres_max REAL NOT NULL,
    graisses_sat_max REAL NOT NULL,
    proteines_min REAL NOT NULL,
    proteines_max REAL NOT NULL,
    fibres_min REAL NOT NULL,
    fibres_max REAL NOT NULL,
    sodium_max REAL NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    taille REAL NOT NULL DEFAULT 0,
    poids REAL NOT NULL DEFAULT 0,
    annee INTEGER NOT NULL DEFAULT 0,
    sexe VARCHAR(10) NOT NULL DEFAULT '',
    activite VARCHAR(20) NOT NULL DEFAULT '',
    imc REAL NOT NULL DEFAULT 0,
    objectif VARCHAR(20) NOT NULL DEFAULT 'perte',
    glucides REAL NOT NULL DEFAULT 0,
    graisses_insaturees REAL NOT NULL DEFAULT 0,
    date_debut DATE,
    date_fin DATE DEFAULT NULL,
    actif BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: historique_mesures
CREATE TABLE historique_mesures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    date_mesure DATE NOT NULL,
    poids DECIMAL(5,2) NOT NULL,
    imc DECIMAL(4,2) NOT NULL,
    taille DECIMAL(5,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_profiles_user ON profiles(user_id);
CREATE INDEX idx_user_config_user_key ON user_config(user_id, config_key);
CREATE INDEX idx_aliments_category ON aliments(category_id);
CREATE INDEX idx_repas_user_date ON repas(user_id, date_heure);
CREATE INDEX idx_repas_user_type_date ON repas(user_id, type_repas, date_heure);
CREATE INDEX idx_repas_aliments_repas ON repas_aliments(repas_id);
CREATE INDEX idx_repas_aliments_aliment ON repas_aliments(aliment_id);
CREATE INDEX idx_activites_user_date ON activites_physiques(user_id, date_heure);
CREATE INDEX idx_prises_medicament_date ON prises_medicaments(medicament_id, date);
CREATE INDEX idx_objectifs_user_actif ON objectifs_nutrition(user_id, actif);
CREATE INDEX idx_historique_user_date ON historique_mesures(user_id, date_mesure);