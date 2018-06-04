# Worx Landroid S Model

## Introduction
This plugin is dedicated to worx mower Landroid S model

### Plugin setup

Start dependency and daemon if needed.
Enter email and password and save.
Once done, equipment will be created automatically.


### Usage

Available features on dashboard :
- Go home button
- Start button
- Last communication date/time
- Mower status
- Mower error details if any
- Weekly planning
- Off buttons set current timing to 0
- On button retrieves last known times
- Cut edge info


## FAQ

>Compatibility?

Worx Model S Landroid WR10xSx. 


>Liste des codes erreur:
- 1: Bloquée
- 2: Soulevée
- 3: Câble non trouvé
- 4: En dehors des limites
- 5: Délai pluie
- 6: Close door to mow
- 7: Close door to go home
- 8: Moteur lames bloqué
- 9: Moteur roues bloqué
- 10: Timeout après blocage
- 11: Renversée
- 12: Batterie faible
- 13: Câble inversé
- 14: Erreur charge batterie
- 15: Delai recherche station dépassé

>Liste des codes statut:
- 0: Inactive
- 1: Sur la base
- 2: Séquence de démarrage
- 3: Quitte la base
- 4: Suit le câble
- 5: Recherche de la base
- 6: Recherche du câble
- 7: En cours de tonte
- 8: Soulevée
- 9: Coincée
- 10: Lames bloquées
- 11: Debug
- 12: Remote control
- 30: Retour à la base
- 32: Coupe la bordure

## How is the connection established?

Below APIs get connection parameters to mosquitto broker
https://api.worxlandroid.com:443/api/v1/users/auth
https://api.worxlandroid.com:443/api/v1/users/certificate
https://api.worxlandroid.com:443/api/v1/product-items

Public key:
https://www.symantec.com/content/en/us/enterprise/verisign/roots/VeriSign-Class%203-Public-Primary-Certification-Authority-G5.pem




## Changelog

[See change log page](changelog_en.md)
