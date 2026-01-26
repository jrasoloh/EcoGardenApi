# 🌿 EcoGarden API

API REST développée avec **Symfony 7** pour l'application de jardinage EcoGarden.
Ce projet fournit un backend robuste pour la gestion des utilisateurs, des conseils de jardinage saisonniers et un service météo localisé intelligent.

## 🚀 Fonctionnalités Clés

* **Authentification & Sécurité** :
    * Authentification via **JWT (JSON Web Token)**.
    * Gestion des rôles (`ROLE_USER`, `ROLE_ADMIN`).
    * Hashage des mots de passe.
    * Validation stricte des données (Whitelisting des rôles, unicité du login).
* **Service Météo Intelligent** :
    * Connexion à l'API **OpenWeatherMap**.
    * Système de **Cache** (1 heure) pour optimiser les performances et limiter les appels API.
    * Route d'administration pour **forcer le vidage du cache**.
* **Architecture "Clean"** :
    * Séparation des responsabilités : **Controller ↔ Manager ↔ Service ↔ Repository**.
    * **EventSubscriber** global pour transformer toutes les erreurs (Exceptions) en réponses JSON standardisées.
* **CRUD Complets** : Gestion des utilisateurs et des conseils de jardinage.

---

## 🛠 Prérequis

* PHP 8.1 ou supérieur
* Composer
* Symfony CLI
* MySQL
* Une clé API **OpenWeatherMap** (Gratuite)

---

## ⚙️ Installation

### 1. Cloner le projet
```bash
git clone <url-du-repo>
cd EcoGardenApi
```

### 2. Installer les dépendances
```bash
composer install  
``` 

### 3. Configuration de l'environnement
Dupliquez le fichier .env pour créer votre configuration locale :

```bash
cp .env .env.local
``` 

#### Ouvrez .env.local et configurez :
- DATABASE_URL : Vos accès à la base de données.
- WEATHER_API_KEY : Votre clé API OpenWeatherMap.

### 4. Générer les clés JWT (Sécurité)
Indispensable pour la création des tokens d'authentification :
```bash
php bin/console lexik:jwt:generate-keypair
```

### 5. Créer la base de données et exécuter les migrations
```bash
# Création de la base
php bin/console doctrine:database:create

# Création des tables
php bin/console doctrine:migrations:migrate

# Chargement des fausses données (Fixtures)
php bin/console doctrine:fixtures:load
```
Note : Les fixtures créent automatiquement un administrateur par défaut :

- Login : admin 
- Password : test 

## 📡 Documentation de l'API
### 🔐 Authentification

| Méthode | Endpoint | Description | Body (JSON) |
| :--- | :--- | :--- | :--- |
| `POST` | `/register` | Création de compte | `{"login": "...", "password": "...", "city": "..."}` |
| `POST` | `/auth` | Connexion (Token) | `{"login": "...", "password": "..."}` |

### 🌤 Météo

| Méthode | Endpoint | Description | Accès |
| :--- | :--- | :--- | :--- |
| `GET` | `/meteo/{city}` | Météo actuelle (Cache ou Live) | Connecté |
| `DELETE`| `/admin/cache/meteo/{city}` | **Vider le cache** manuellement | **Admin** |

### 👤 Utilisateurs

| Méthode | Endpoint | Description | Accès |
| :--- | :--- | :--- | :--- |
| `PUT` | `/user/{id}` | Modifier un profil (Login, Ville, Rôles) | **Admin** |
| `DELETE`| `/user/{id}` | Supprimer un utilisateur | **Admin** |
Note sur le PUT : Il est possible de promouvoir un utilisateur en envoyant {"roles": ["ROLE_ADMIN"]}.

### 🌿 Conseils

| Méthode | Endpoint | Description | Accès |
| :--- | :--- | :--- | :--- |
| `GET` | `/conseil` | Lister les conseils | Connecté |
| `POST` | `/conseil` | Créer un conseil | **Admin** |
| `PUT` | `/conseil/{id}` | Modifier un conseil | **Admin** |
| `DELETE`| `/conseil/{id}` | Supprimer un conseil | **Admin** |

## ✅ Tests (Postman)
Une collection complète est fournie pour tester l'API sans configuration manuelle.

Rendez-vous dans le dossier /postman à la racine du projet.
Importez les fichiers .json dans votre application Postman.
Utilisez l'environnement fourni (ou configurez la variable {{base_url}} sur http://127.0.0.1:8000).

## 🏗 Choix Techniques & Architecture
Le projet respecte une architecture en couches pour garantir la maintenabilité :

1. Controller : Point d'entrée HTTP. Il ne contient aucune logique métier. Il valide le format de la requête et appelle le Manager.
2. Manager (UserManager, ConseilManager) : Cœur du métier. Il gère la logique, les règles de validation (ex: unicité du login) et appelle l'Entity Manager. Il lance des Exceptions en cas d'erreur.
3. Service (MeteoService) : Gère les appels aux API externes et la logique de Caching.
4. EventSubscriber (ApiExceptionSubscriber) : Intercepte toutes les Exceptions du noyau (403, 404, 500, etc.) pour renvoyer systématiquement une réponse JSON propre au client.
