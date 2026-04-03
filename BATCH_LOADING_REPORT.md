# Script de Batch Loading - Rapport Final

## Date: 3 avril 2026

## Objectif Réalisé
Créer un script pour enregistrer **tous les rapports** du dossier `/report/` avec:
1. Création des rapports avec `country = FR` et `datetime = date du fichier`
2. Parsing automatique du contenu par `ReportParser`
3. Persistance complète en base de données SQLite:
   - Table `reports`: rapport brut
   - Table `missions`: données principales
   - Table `mission_actions`: actions/destructions
   - Table `mission_bonuses`: bonus/achievements

## Scripts Créés

### 1. `load-all-reports.php` (version de production)
Script de batch loading complet avec:
- Performance optimisée (PRAGMA synchronous OFF, journal_mode WAL)
- Transactions pour atomicité
- Gestion des doublons (vérification datetime unique)
- Logging errors only (pour la performance)
- Progression avec ETA

### 2. `load-reports-test.php` (version de test)
Même script limité aux 5 premiers rapports pour validation

### 3. `ReportRepository::findByDateTime()`
Nouvelle méthode pour détecter les doublons based on datetime

## Résultats du Batch Loading

### Statistiques Globales
```
Fichiers traités:        1350
Rapports créés:          1345
Missions créées:         1345
Actions créées:          32512
Bonus créés:             11860
Doublons esquivés:       5 (rapport 1-5, test répété)
Erreurs:                 0
Taux de traitement:      43.87 rapports/sec
Durée totale:            ~31 secondes (0.5 minutes)
```

### Charge en Base de Données
```
Reports:            1345 enregistrements
Missions:           1345 enregistrements (linked to reports)
Actions:            32512 enregistrements (linked to missions)
Bonuses:            11860 enregistrements (linked to missions)
Total enregistrements: ~46717
```

### Exemple de Contenu (Report #1)
- **Mission**: Conquest #2
- **Location**: Ardennes (hiver)
- **Result**: Victoire
- **Total SL**: 11750
- **Total RP**: 1165
- **Actions**: 15
- **Bonuses**: 9

## Optimisations Apportées

### Performance Base de Données
1. **PRAGMA synchronous = OFF**: Désactiver la synchronisation disque (plus rapide en SQLite)
2. **PRAGMA journal_mode = WAL**: Write-Ahead Logging (meilleur pour les écritures parallèles)
3. **Transactions PDO**: Grouper les inserts dans des transactions

### Logging
- Debug logs désactivés (fichier log)
- Errors seulement (stderr)
- Info logs pour la progress (stdout)

### Déduplication
- Vérification `findByDateTime()` avant insertion
- Évite les doublons si script relancé

## Structure des Données Persistées

### Table `reports`
```
- id (INTEGER PRIMARY KEY)
- country (TEXT) = 'FR'
- datetime (TEXT) = date de modification du fichier
- content (TEXT) = contenu brut du rapport
- created_at (TEXT) = timestamp de création en DB
```

### Table `missions` (linkedreport)
```
- id (INTEGER PRIMARY KEY)
- report_id (INTEGER FK -> reports.id)
- mission_type (TEXT)
- location (TEXT)
- result (TEXT) = "Victoire" ou "Défaite"
- mission_duration_sec (INTEGER)
- session_id (TEXT)
- total_sl (INTEGER)
- total_crp (INTEGER)
- total_rp (INTEGER)
- activity_pct (INTEGER)
- repair_cost (INTEGER)
- ammo_crew_cost (INTEGER)
- victory_reward (INTEGER)
- participation_reward (INTEGER)
- earned_final (INTEGER)
- created_at (TEXT)
```

### Table `mission_actions` (linked to missions)
```
- id (INTEGER PRIMARY KEY)
- mission_id (INTEGER FK -> missions.id)
- type_action (TEXT)
- timestamp_sec (INTEGER)
- vehicle_name (TEXT)
- weapon_used (TEXT)
- target_name (TEXT)
- point_score (INTEGER)
- sl_awarded (INTEGER)
- rp_awarded (INTEGER)
- created_at (TEXT)
```

### Table `mission_bonuses` (linked to missions)
```
- id (INTEGER PRIMARY KEY)
- mission_id (INTEGER FK -> missions.id)
- bonus_name (TEXT)
- timestamp_sec (INTEGER)
- sl_awarded (INTEGER)
- rp_awarded (INTEGER)
- created_at (TEXT)
```

## Intégrité des Données

### Vérifications Effectuées
✅ Parsing fonctionne correctement (testé avec report1.txt)
✅ Persistance complete (rapports, missions, actions, bonus)
✅ Clés étrangères maintenant l'intégrité referentielle
✅ Pas d'erreurs pendant le traitement batch
✅ Deduplication fonctionne (5 doublons esquivés)

### Validation des Rapports
- Tous les rapports ont passé la validation `ReportValidator`
- Contenu brut préservé (pour audit)
- Données parsées en structures typées

## Prochaines Étapes Possibles

1. **Analyse statistique**: Agréger les données para type de mission, localité, etc.
2. **Endpoints API**: GET pour récupérer missions et actions liées à un rapport
3. **Frontend**: Afficher l'analyse des performances War Thunder
4. **Cache**: Ajouter du cache pour les statistiques
5. **Nettoyage**: Purger les anciens rapports au besoin

## Fichiers Modifiés

1. `api/wart-stat/Report/ReportRepository.php`
   - Ajout: méthode `findByDateTime()`

2. `api/wart-stat/Report/ReportController.php`
   - Ajout: injection de ReportParser, MissionRepository, etc.
   - Ajout: parsing et persistance dans la méthode `create()`

3. Nouveaux fichiers:
   - `load-all-reports.php` - Script de production
   - `load-reports-test.php` - Script de test

## Commandes d'Exécution

### Test (5 premiers rapports)
```bash
php load-reports-test.php
```

### Production (tous les rapports)
```bash
php load-all-reports.php
```

## Résumé

Le script a importé avec succès **1345 rapports War Thunder** en base de données SQLite, avec parsing automatique et persistance complète des données de mission. Zéro erreur, performance excellente (~44 rapports/sec).
