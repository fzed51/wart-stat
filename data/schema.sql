-- ============================================
-- War Thunder Reports Database Schema
-- SQLite Initialisation Script
-- ============================================

-- Drop existing tables if they exist (for clean reset)
DROP TABLE IF EXISTS active_boosters;
DROP TABLE IF EXISTS research_progress;
DROP TABLE IF EXISTS research_target;
DROP TABLE IF EXISTS play_time;
DROP TABLE IF EXISTS activity_time;
DROP TABLE IF EXISTS skill_bonuses;
DROP TABLE IF EXISTS mission_bonuses;
DROP TABLE IF EXISTS actions;
DROP TABLE IF EXISTS weapons;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS bonus_types;
DROP TABLE IF EXISTS mission_types;
DROP TABLE IF EXISTS missions;

-- ============================================
-- CORE TABLES
-- ============================================

-- Mission Types Lookup Table
CREATE TABLE mission_types (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_type    TEXT NOT NULL UNIQUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles Lookup Table
CREATE TABLE vehicles (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    vehicle_name    TEXT NOT NULL UNIQUE,
    vehicle_tier    INTEGER,
    nation          TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Weapons Lookup Table
CREATE TABLE weapons (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    weapon_name     TEXT NOT NULL UNIQUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bonus Types Lookup Table
CREATE TABLE bonus_types (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    bonus_name      TEXT NOT NULL UNIQUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Main Missions Table
CREATE TABLE missions (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id              TEXT NOT NULL,
    mission_type_id         INTEGER NOT NULL,
    location                TEXT NOT NULL,
    result                  TEXT NOT NULL CHECK (result IN ('Victoire', 'Défaite')),
    total_sl                INTEGER NOT NULL,
    total_crp               INTEGER NOT NULL,
    total_rp                INTEGER NOT NULL,
    activity_pct            INTEGER NOT NULL DEFAULT 0,
    repair_cost             INTEGER DEFAULT 0,
    ammo_crew_cost          INTEGER NOT NULL DEFAULT 0,
    victory_reward          INTEGER,
    participation_reward    INTEGER,
    earned_final            INTEGER NOT NULL,
    damaged_vehicle_list    TEXT,
    rescue_used             TEXT,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_type_id) REFERENCES mission_types(id)
);

-- Actions Table (All action types consolidated)
CREATE TABLE actions (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id          INTEGER NOT NULL,
    type_action         TEXT NOT NULL,
    timestamp_sec       INTEGER NOT NULL,
    vehicle_id          INTEGER NOT NULL,
    weapon_used         TEXT,
    target_name         TEXT,
    point_score         INTEGER,
    sl_awarded          INTEGER NOT NULL DEFAULT 0,
    rp_awarded          INTEGER NOT NULL DEFAULT 0,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- ============================================
-- BONUS TABLES
-- ============================================

-- Mission Bonuses
CREATE TABLE mission_bonuses (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id      INTEGER NOT NULL,
    bonus_type_id   INTEGER NOT NULL,
    timestamp_sec   INTEGER NOT NULL,
    sl_awarded      INTEGER NOT NULL DEFAULT 0,
    rp_awarded      INTEGER DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (bonus_type_id) REFERENCES bonus_types(id)
);

-- Skill Bonuses per Vehicle
CREATE TABLE skill_bonuses (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id      INTEGER NOT NULL,
    vehicle_id      INTEGER NOT NULL,
    skill_level     TEXT NOT NULL,
    rp_awarded      INTEGER NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    UNIQUE(mission_id, vehicle_id)
);

-- ============================================
-- TIME/ACTIVITY TABLES
-- ============================================

-- Activity Time
CREATE TABLE activity_time (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id      INTEGER NOT NULL,
    vehicle_id      INTEGER NOT NULL,
    sl_awarded      INTEGER NOT NULL DEFAULT 0,
    rp_awarded      INTEGER NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    UNIQUE(mission_id, vehicle_id)
);

-- Play Time
CREATE TABLE play_time (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id      INTEGER NOT NULL,
    vehicle_id      INTEGER NOT NULL,
    percentage      INTEGER NOT NULL,
    duration_sec    INTEGER NOT NULL,
    rp_awarded      INTEGER NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    UNIQUE(mission_id, vehicle_id)
);

-- ============================================
-- RESEARCH TABLES
-- ============================================

-- Research Target
CREATE TABLE research_target (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id      INTEGER NOT NULL UNIQUE,
    target_name     TEXT NOT NULL,
    total_rp_earned INTEGER NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
);

-- Research Progress Detail
CREATE TABLE research_progress (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id              INTEGER NOT NULL,
    contributing_vehicle_id INTEGER NOT NULL,
    research_target_type    TEXT NOT NULL CHECK (research_target_type IN ('vehicle', 'module')),
    research_target_name    TEXT NOT NULL,
    rp_contribution         INTEGER NOT NULL DEFAULT 0,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    FOREIGN KEY (contributing_vehicle_id) REFERENCES vehicles(id)
);

-- ============================================
-- BOOSTER TABLES
-- ============================================

-- Active Boosters
CREATE TABLE active_boosters (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id      INTEGER NOT NULL,
    booster_type    TEXT NOT NULL CHECK (booster_type IN ('SL', 'RP')),
    booster_rarity  TEXT,
    total_percentage INTEGER NOT NULL DEFAULT 0,
    details         TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
);

-- ============================================
-- INDEXES
-- ============================================

CREATE INDEX idx_missions_session_id ON missions(session_id);
CREATE INDEX idx_missions_result ON missions(result);
CREATE INDEX idx_missions_mission_type_id ON missions(mission_type_id);
CREATE INDEX idx_missions_created_at ON missions(created_at);

CREATE INDEX idx_actions_mission_id ON actions(mission_id);
CREATE INDEX idx_actions_vehicle_id ON actions(vehicle_id);
CREATE INDEX idx_actions_type_action ON actions(type_action);

CREATE INDEX idx_mission_bonuses_mission_id ON mission_bonuses(mission_id);
CREATE INDEX idx_mission_bonuses_bonus_type_id ON mission_bonuses(bonus_type_id);

CREATE INDEX idx_skill_bonuses_mission_id ON skill_bonuses(mission_id);
CREATE INDEX idx_skill_bonuses_vehicle_id ON skill_bonuses(vehicle_id);

CREATE INDEX idx_activity_time_mission_id ON activity_time(mission_id);
CREATE INDEX idx_activity_time_vehicle_id ON activity_time(vehicle_id);

CREATE INDEX idx_play_time_mission_id ON play_time(mission_id);
CREATE INDEX idx_play_time_vehicle_id ON play_time(vehicle_id);

CREATE INDEX idx_research_target_mission_id ON research_target(mission_id);

CREATE INDEX idx_research_progress_mission_id ON research_progress(mission_id);
CREATE INDEX idx_research_progress_vehicle_id ON research_progress(contributing_vehicle_id);
