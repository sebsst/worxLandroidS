# Changelog

En cas d'absence de note dans ce chapitre, les mises à jour ne concernent que la doc, les traductions et des corrections mineures

## 11/09/2018

- Ajout du paramètre type de tondeuses: Landroid version S / landroid version M (firmware 5.x)
- Ajout de la fonction "pause"

## 09/07/2018

- Possibilité de définir son propre widget pour les commandes de type infos pour permettre l'affichage de données supplémentaires
- Modification des types d'info numériques (peut-être aussi fait manuellement ou en recréant l'équipement)

## 03/07/2018

- Ajout message création équipement 
- Message si version mosquitto non compatible
- correction bug affichage zone de départ

## 25/06/2018

- correction bug message sur cron30
- nouvelle tentative en cas après 15 secondes pour éviter certaines fausses alertes

## 16/06/2018

- modification du script d'installation pour tenter de résoudre les problème de version de mosquitto (version mini 1.4.1)
- Installation version mosquitto 1.5 si version mosquitto 1.3

## 14/06/2018

- Corrections des fonctions démarrer/arrêter.
- Modifications timeout si le serveur mosquitto n'envoie aucun message 
- changement du délai pluie manquant dans certains cas

## 09/06/2018

Ajout de nouvelles actions: 
- Ajout des délais de tonte après une pluie
- Ajout des actions off_today / on_today pour faciliter la gestion de l'activité du jour par scénarios (pour les jours fériés par exemple)

Autres modifications:
- Widget désormais modifiable (couleur/transparence...)
- Possibilité d'enlever certaines infos: errorCode, statusCode, totalDistance, batteryChargeCycle, rainDelay
- Affichage de la prochaine zone de tonte. C'est la zone de départ de la prochaine tonte ou de celle en cours. 
- Changement des infos en numérique pour permettre de faire des statistiques (évolution de la batterie par exemple)

## 06/06/2018

Modification des fréquences de mise à jour des infos: 
- Toutes les 2 minutes pendant la tonte
- Toutes les 30 minutes en dehors des periodes de tonte
- sur demande ou envoi de mise à jour du planning de fonctionnement.

## 04/06/2018

- Changement délai daemon et autres paramètres de connexion au serveur worx

## 01/06/2018

- Ajustement design widget
- remplacement id client mosquitto

## Mai 2018

Création du plugin

