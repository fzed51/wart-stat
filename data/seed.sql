-- ============================================
-- Seed Data for Lookups
-- ============================================

-- Mission Types
INSERT OR IGNORE INTO mission_types(mission_type) VALUES 
('Attaque au sol ⋣'),
('Attaque au sol ⋩'),
('Attaque au sol ⋣/⋩'),
('Battle'),
('Battle #2'),
('Conquest'),
('Conquest #1'),
('Conquest #2'),
('Conquest #3'),
('Domination'),
('Domination #1'),
('Domination #2'),
('┚ Domination'),
('┚ Domination #2');

-- Bonus Types (Sample)
INSERT OR IGNORE INTO bonus_types(bonus_name) VALUES
('Sauveur de chars'),
('Sauveur de forces au sol'),
('Sauveur de chasseurs'),
('Vengeur'),
('Oeil pour oeil'),
('Inebranlable'),
('La meilleure escouade'),
('Travail d equipe'),
('Appui feu'),
('Serie de frappes furtives'),
('Frappe multiple'),
('Sans manquer un tir'),
('Renseignements'),
('Selon les renseignements'),
('Le rang n importe pas'),
('La competence l emporte'),
('Equilibreur'),
('Professionnel');
