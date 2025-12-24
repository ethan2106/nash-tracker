CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT 1
);

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

CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE
);

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

CREATE TABLE consommation_eau (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    quantite_ml INTEGER NOT NULL,
    date_heure DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE activites_physiques (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type_activite VARCHAR(50) NOT NULL,
    duree_minutes INTEGER NOT NULL,
    calories_depensees INTEGER NOT NULL,
    date_heure DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

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

CREATE TABLE prises_medicaments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    medicament_id INTEGER NOT NULL,
    date DATE NOT NULL,
    periode VARCHAR(10) NOT NULL CHECK(periode IN ('matin', 'midi', 'soir', 'nuit')),
    status VARCHAR(4) NOT NULL DEFAULT 'non' CHECK(status IN ('pris', 'non')),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE CASCADE
);

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

CREATE TABLE walks (
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
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE walk_objectives (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    km_per_day REAL NOT NULL DEFAULT 5.00,
    days_per_week INTEGER NOT NULL DEFAULT 4,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE walk_routes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    distance_km REAL NOT NULL,
    route_points TEXT NOT NULL, -- JSON array de {lat, lng}
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

