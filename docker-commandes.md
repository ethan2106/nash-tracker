Voici les principales commandes de “nettoyage” Docker à connaître, avec leur rôle :

- **Nettoyer les conteneurs arrêtés**  
  ```bash
  docker container prune
  ```  
  Supprime tous les conteneurs arrêtés. Possibilité de filtrer (ex. par date) avec `--filter`. [[Prune containers](https://docs.docker.com/engine/manage-resources/pruning/); [container prune ref](https://docs.docker.com/reference/cli/docker/container/prune/)]

- **Supprimer des conteneurs précis**  
  ```bash
  docker rm <container_id>
  docker container rm <container_id>
  ```  
  Supprime un ou plusieurs conteneurs donnés (option `-f` pour forcer, `-v` pour supprimer aussi les volumes anonymes associés). [[container rm](https://docs.docker.com/reference/cli/docker/container/rm/)]

- **Nettoyer les images inutilisées**  
  ```bash
  docker image prune          # images "dangling" (sans tag, non référencées)
  docker image prune -a       # toutes les images non utilisées par un conteneur
  ```  
  Peut être combiné avec `--filter "until=24h"` par exemple. [[Prune images](https://docs.docker.com/engine/manage-resources/pruning/); [image prune ref](https://docs.docker.com/reference/cli/docker/image/prune/)]

- **Supprimer des images précises**  
  ```bash
  docker rmi <image>
  docker image rm <image>
  ```  
  Supprime une ou plusieurs images (par ID, tag ou digest). [[image rm](https://docs.docker.com/reference/cli/docker/image/rm/); [image rm examples](https://docs.docker.com/reference/cli/docker/image/rm/#examples)]

- **Nettoyer les volumes inutilisés**  
  ```bash
  docker volume prune
  ```  
  Supprime tous les volumes non utilisés par au moins un conteneur (avec possibilité de filtres). [[Prune volumes](https://docs.docker.com/engine/manage-resources/pruning/)]

- **Nettoyage global (tout en une fois)**  
  ```bash
  docker system prune
  ```  
  Supprime :  
  - tous les conteneurs arrêtés  
  - tous les réseaux non utilisés  
  - toutes les images “dangling”  
  - le cache de build inutilisé [[Prune everything](https://docs.docker.com/engine/manage-resources/pruning/#prune-everything); [system prune ref](https://docs.docker.com/reference/cli/docker/system/prune/)]

  Avec options utiles :  
  ```bash
  docker system prune -a          # inclut toutes les images inutilisées
  docker system prune --volumes   # inclut les volumes anonymes inutilisés
  docker system prune -f          # sans demande de confirmation
  ```

- **Avec Docker Compose** (pour un projet)  
  ```bash
  docker compose down
  ```  
  Arrête et supprime les conteneurs et réseaux créés par `docker compose up`.  
  Options utiles :  
  - `-v` : supprime aussi les volumes déclarés et anonymes  
  - `--rmi local|all` : supprime les images utilisées par les services [[compose down](https://docs.docker.com/reference/cli/docker/compose/down/)]

Si tu précises ton cas (dev local, CI, serveur de prod, etc.), je peux te proposer un petit “script” ou une routine de nettoyage adaptée.