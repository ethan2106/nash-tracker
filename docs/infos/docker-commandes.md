# Commandes de nettoyage Docker

## Conteneurs

- **Supprimer les conteneurs arrêtés** :
  ```bash
  docker container prune
  ```

- **Supprimer des conteneurs spécifiques** :
  ```bash
  docker rm <container_id>
  docker container rm <container_id>
  ```
  Options : `-f` pour forcer, `-v` pour supprimer les volumes associés.

## Images

- **Supprimer les images inutilisées** :
  ```bash
  docker image prune          # images "dangling"
  docker image prune -a       # toutes les images non utilisées
  ```

- **Supprimer des images spécifiques** :
  ```bash
  docker rmi <image>
  docker image rm <image>
  ```

## Volumes

- **Supprimer les volumes inutilisés** :
  ```bash
  docker volume prune
  ```

## Cache de build

- **Nettoyer le cache de build** :
  ```bash
  docker builder prune
  docker builder prune -a      # tout le cache inutilisé
  ```

## Logs

- **Vider les logs de tous les conteneurs** :
  ```bash
  sudo find /var/lib/docker/containers -name "*.log" -exec truncate -s 0 {} \;
  ```
  Vide les logs sans supprimer les conteneurs (accès root requis sur l'hôte).

  **Sous Windows (Docker Desktop/WSL)** :
  - Ouvre PowerShell en tant qu'administrateur.
  - Lance WSL en root : `wsl -u root`
  - Exécute la commande sans `sudo`.

## Nettoyage global

- **Nettoyer tout (conteneurs, images, réseaux, cache)** :
  ```bash
  docker system prune
  ```
  Options :
  - `-a` : inclut toutes les images inutilisées
  - `--volumes` : inclut les volumes anonymes
  - `-f` : sans confirmation

- **Nettoyage complet (très destructif)** :
  ```bash
  docker system prune -a --volumes
  ```

## Docker Compose

- **Arrêter et supprimer les conteneurs d'un projet** :
  ```bash
  docker compose down
  ```
  Options :
  - `-v` : supprime les volumes
  - `--rmi local` : supprime les images locales

## Résumé rapide

Pour repartir propre :
```bash
docker system prune -a --volumes
docker builder prune -a
```

Pour nettoyer seulement les éléments vieux (ex. >24h) :
```bash
docker system prune -a --volumes --filter "until=24h"
docker builder prune -a --filter "until=24h"
```

