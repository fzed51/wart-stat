-- ============================================
-- Test Data for Schema Validation
-- ============================================

-- Insert test vehicles
INSERT INTO vehicles (vehicle_name, vehicle_tier, nation) VALUES
('M103', 9, 'USA'),
('Tiger Ost', 6, 'Germany'),
('XM803', 8, 'USA'),
('Re.2005 (VDM)', 5, 'Italy'),
('Olifant Mk 2', 7, 'Netherlands');

-- INSERT test mission (report1.txt equivalent)
INSERT INTO missions (
    session_id, mission_type_id, location, result,
    total_sl, total_crp, total_rp, activity_pct,
    repair_cost, ammo_crew_cost, earned_final,
    victory_reward
) VALUES (
    '5dac257003b3ac2', 8, 'Ardennes (hiver)', 'Victoire',
    23920, 3768, 4791, 85,
    -7541, -900, 15479,
    7282
);

-- Insert test actions (sample from report1.txt)
INSERT INTO actions (mission_id, type_action, timestamp_sec, vehicle_id, weapon_used, target_name, point_score, sl_awarded, rp_awarded) VALUES
(1, 'destruction_terrestre', 220, 1, 'M358 shot', 'Objet 140', 200, 2610, 255),
(1, 'destruction_terrestre', 250, 1, 'M358 shot', 'Vickers Mk 11', 200, 2610, 255),
(1, 'destruction_terrestre', 395, 3, 'XM578E1', 'Type 74 (E)', 200, 2610, 260),
(1, 'assist', 156, 1, 'M358 shot', 'T-62', 120, 1045, 105),
(1, 'assist', 411, 1, 'M358 shot', 'T-55AMD', 120, 1045, 109),
(1, 'critical_hit', 220, 1, 'M358 shot', 'Objet 140', 60, 261, 25),
(1, 'critical_hit', 250, 1, 'M358 shot', 'Vickers Mk 11', 60, 261, 25);

-- Insert activity times
INSERT INTO activity_time (mission_id, vehicle_id, sl_awarded, rp_awarded) VALUES
(1, 1, 2164, 210),
(1, 2, 84, 8),
(1, 3, 1590, 158);

-- Insert play times
INSERT INTO play_time (mission_id, vehicle_id, percentage, duration_sec, rp_awarded) VALUES
(1, 1, 91, 267, 1348),
(1, 2, 0, 41, 0),
(1, 3, 72, 149, 665);

-- Insert skill bonuses
INSERT INTO skill_bonuses (mission_id, vehicle_id, skill_level, rp_awarded) VALUES
(1, 1, 'I', 145),
(1, 2, 'I', 1),
(1, 3, 'I', 68);

-- Insert research targets
INSERT INTO research_target (mission_id, target_name, total_rp_earned) VALUES
(1, 'M10 Booker', 2234);

-- Insert research progress
INSERT INTO research_progress (mission_id, contributing_vehicle_id, research_target_type, research_target_name, rp_contribution) VALUES
(1, 1, 'module', 'Télémètre', 2557);

-- Insert mission bonuses (Prix)
INSERT INTO mission_bonuses (mission_id, bonus_type_id, timestamp_sec, sl_awarded) VALUES
(1, 1, 220, 75),
(1, 4, 251, 225);

-- Insert active boosters
INSERT INTO active_boosters (mission_id, booster_type, booster_rarity, total_percentage, details) VALUES
(1, 'RP', 'Commun', 50, '[{"percent": 50, "source": "personnel"}]');
