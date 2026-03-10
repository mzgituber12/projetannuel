# Projet Annuel

### Executer le code pour Docker : 

#### D'abord

* docker-compose down (pas obligatoire mais a préférer en cas de bug)
* docker-compose build 
* docker-compose up -d 

#### Ensuite :

* localhost -> site internet 
* localhost:8081 -> base de donnée 

### Commandes pour Github

#### Mettre sur Github

* Aller sur le dossier du projet
* Git add . (pour tous les fichiers & dossiers)
* Git commit -m "le message que tu veux"
* Git push

#### Installer le projet via Github

* Cliquer sur l'icone verte ou y'a ecrit Code
* Cliquer sur Download ZIP

### En cas de bug sur la base de donnée :

* Supprimer le dossier data dans db
* Exécuter les commandes docker a nouveau, le dossier data va se recreer tout seul

## Verifier les erreurs http de :

- inscription
- gestion_contact
- gestion_intervention
- gestion_service
- catalogue
- api_config
- modifier_service
- modifier_intervention
- header
- footer

### En cas de probleme d'updates JavaScript

* CTRL + F5
