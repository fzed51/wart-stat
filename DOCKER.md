# Guide Docker - Wart-Stat

## Architecture

Le projet utilise Docker Compose pour orchestrer 3 services:

1. **Frontend** - Application React/TypeScript servie via Node.js
2. **Backend** - API PHP Slim servie via PHP-FPM
3. **Nginx** - Reverse proxy pour router les requêtes

## Prérequis

- Docker Desktop (version 20.10+)
- Docker Compose (version 2.0+)
- Au minimum 4GB de RAM disponibles

## Configuration initiale

### 1. Cloner le repository
```bash
git clone <repository-url>
cd wart-stat
```

### 2. Préparer les fichiers

Assurez-vous d'avoir un fichier `composer.lock`:
```bash
composer install --no-dev
```

Assurez-vous d'avoir `yarn.lock`:
```bash
yarn install
```

## Commandes Docker Compose

### Démarrer les services
```bash
docker-compose up -d
```

Ou avec la construction des images:
```bash
docker-compose up -d --build
```

### Voir les logs
```bash
# Tous les services
docker-compose logs -f

# Service spécifique
docker-compose logs -f frontend
docker-compose logs -f backend
docker-compose logs -f nginx
```

### Arrêter les services
```bash
docker-compose down
```

### Supprimer tout (images, volumes, containers)
```bash
docker-compose down -v
```

### Reconstruire les images
```bash
docker-compose build --no-cache
```

## Accès à l'application

- **Frontend**: http://localhost:3000
- **API Backend**: http://localhost:9000
- **Health Check**: http://localhost/health

## Services détails

### Frontend (Node.js)
- **Port**: 3000
- **Health Check**: Chaque 30 secondes
- **Commande**: `serve -s dist -l 3000`
- **Dockerfile**: Utilise un build multi-stage pour minimiser la taille

### Backend (PHP-FPM)
- **Port**: 9000 (interne, exposé)
- **Health Check**: Chaque 30 secondes
- **Extensions PHP**: opcache activé
- **Volumes**: `./report` pour la persistance des données

### Nginx
- **Ports**: 80 (HTTP) et 443 (HTTPS)
- **Rôle**: Reverse proxy et load balancer
- **Configuration**: `nginx.conf` dans la racine du projet

## Configuration SSL/TLS (Optional)

Pour utiliser HTTPS en production:

1. Placez vos certificats dans un dossier `ssl/`:
   - `ssl/cert.pem` - Certificat
   - `ssl/key.pem` - Clé privée

2. Décommentez les lignes SSL dans `nginx.conf`:
```nginx
ssl_certificate /etc/nginx/ssl/cert.pem;
ssl_certificate_key /etc/nginx/ssl/key.pem;
```

3. Redémarrez les services:
```bash
docker-compose down
docker-compose up -d
```

## Gestion des volumes

### Données persistantes

Les données du dossier `report/` sont persistantes grâce au volume:
```yaml
volumes:
  - ./report:/app/report:rw
```

Pour nettoyer les volumes:
```bash
docker-compose down -v
```

## Dépannage

### Les services ne démarrent pas

Vérifiez les logs:
```bash
docker-compose logs backend
docker-compose logs frontend
docker-compose logs nginx
```

### Problème de connexion API

1. Vérifiez que le backend est en cours d'exécution:
   ```bash
   docker-compose ps
   ```

2. Testez la connexion:
   ```bash
   curl http://localhost:9000
   ```

### Réinitialiser complètement

```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

## Performance

### Optimisations appliquées

- **Frontend**: Multi-stage build, minification avec Vite
- **Backend**: Opcache PHP activé
- **Nginx**: Gzip compression, caching des assets
- **Health Checks**: Surveillance automatique des services

### Augmenter les ressources

Dans `docker-compose.yaml`, vous pouvez ajouter des limites de ressources:
```yaml
services:
  frontend:
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 512M
```

## Développement local

Pour le développement avec hot reload:

### Frontend (mode dev)
```bash
# Créer un service dev supplémentaire dans docker-compose
docker-compose run --rm frontend yarn dev
```

### Backend (mode dev)
```bash
# Modifier le dockerfile pour inclure xdebug
# Puis utiliser VSCode avec l'extension PHP Debug
```

## Production

Pour déployer en production:

1. Définir les variables d'environnement appropriées
2. Configurer SSL/TLS correctement
3. Utiliser des volumes nommés au lieu de bind mounts
4. Configurer les resource limits
5. Mettre en place un service de monitoring
6. Utiliser des images private registry si nécessaire

## Maintenance

### Mise à jour des dépendances

Frontend:
```bash
docker-compose exec frontend yarn upgrade
```

Backend:
```bash
docker-compose exec backend composer update
```

### Logs et monitoring

Les logs des services sont disponibles via:
```bash
docker-compose logs --follow --timestamps
```

Ou utilisez des outils comme Portainer pour une interface web.
