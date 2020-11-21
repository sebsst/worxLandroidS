# Changelog

En cas d'absence de note dans ce chapitre, les mises à jour ne concernent que la doc, les traductions et des corrections mineures

# 21/11/2020
- Modification de la commande pour la coupe de la bordure (compatibilité des modèles à vérifier)

# 04/08/2020
- correction affichage coordonnées GPS

# 08/06/2020
- correction de l'image landroid rouge
- après les mises à jour vous devez éventuellement enregistrer l'équipement et peut-être  réinitialiser les données du cloud (configuration du plugin). erreur execcmd / cmd=>getId().
- attention la tondeuse doit être connectée, sinon le plugin ne pourra pas se mettre correctement à jour


# 06/06/2020
- ajout onglet gestion des zones de tonte. (distance de départ + % répartition selon chaque zone)
- possibilité de masquer les infos inclinaison+direction

# 29/05/2020
- ajout information en cas de non communication avec la tondeuse + 24 hr (dissocier et associer à nouveau sur le compte worx)
- modif saisie des temps.
- ajout historique bouton santé du plugin (reinitialisez les données cloud pour activer la modif)
- ajout information sur l'inclinaison (latérale et frontale) et la direction
- tentative de suppression erreur 500 si la communication n'est pas possible avec la tondeuse

## 23/05/2020
- correction du chemin des images
- modification de backgound color pour V3

## 10/05/2020
- Changement du template.  (reprise des images d'Antoinekl du widget worklandroid + travail de Tektek pour les animations, merci à eux)
- correction pour masquer ou afficher certaines zones (planning_starttime permet de masquer ou afficher le jour dans le planning)
- Edition possible des horaires de tonte depuis le widget
- ajout de la gestion de durée de vie des lames (renseigner la durée de vie estimée dans l'équipement et en enregistrer, puis réinitialiser la durée sur le widget en cliquant sur les lames sous l'indicateur batterie)

## 12/03/2020
- correction pour l'initialisation de l'équipement et 1er refresh des données (+aide de Mips)

## 27/06/2019
- correction date de fin de garantie (à la création de l'équipement)
- suppression code inutile
- correction bug lié à virtualInfo

## 08/05/2019
- Ajout d'une info (virtualInfo) pour concatener plusieurs infos du plugin séparé par des virgules pour l'utilisation du widget worklandroid. Le widget est dispo sur le market des widgets. Recherche équipement pour récupérer l'info d'un autre équipement.
- remplacement des infos planning/xxxx/xxx par planning_xxxxx_xxxx suite à un changement du core jeedom

## 28/04/2019
- Diverses corrections
- Ajout de la fonction set_schedule pour modifier le planning de tonte d'un jour donné. Par défaut l'action n'est pas visible. Le but étant de faire de la planification à l'aide d'un scénario mais il est possible de rendre visible sur le widget si besoin.
- Format attendu: numéro jour;heure départ;durée en minutes;bordure
Exemples :
  * 1;10:00;120;1 => lundi, démarrage à 10:00 pendant 120 minutes, coupe la bordure
  * 0;08:00;300;0 => dimanche, démarrage à 08:00 pendant 300 minutes, ne coupe pas la bordure


## 17/04/2019
- Nouvelle méthode d'authentification

## 03/04/2019
- Ajout coordonnées GPS si disponibles

## 29/03/2019
- Correction sur la fréquence de rafraîchissement des données

## 07/11/2018
- La nouvelle version du plugin nécessite la recréation des équipements, vous devez donc supprimer les équipements existants
- Gestion multi-tondeuses
- Détection automatique du type de tondeuse
- suppression mode retry

## 14/09/2018

- Correction de l'affichage sur mobile si le format du widget choisi est le "standard" jeedom.
("Utiliser le widget préconfiguré" est décoché)

## 11/09/2018

- Ajout du paramètre type de tondeuses: Landroid version S / landroid version M (firmware 5.x)
(en cas de soucis vous pouvez cocher réinitialiser les paramètres dans la configuration du plugin et sauvegarder)
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
