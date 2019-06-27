# Robots tondeuses Worx Landroid

## Introduction

Ce plugin permet de se connecter aux tondeuses worx Landroid modèles WIFI

### Plugin configuration

Connexion ids to cloud server are the ones provided during product registration

Email and password are the same than the mobile application. Dependencies installation must be completed before communication can be established with mower.

If all provided are ok, devices will be created automatically after configuration save. Clouds parameters can be refreshed by using the appropriate checkbox

Stopping the daemon will stop periodic communication with the mower. 

If device is not used during a long period, the best solution is to disable plugin to avoid unecessary system resource usage.

### Usage

Device default name = device name from mobile application

Dashboard
- Remaining battery
- Go home
- Start button
- Pause
- Data refresh
- Last communication date and time
- Total distance and work duration
- Total number of charging cycle
- Rain delay in minutes
- Change rain delay (0 = no wait time, 30, 60, 120, 240)
- Mower status and status code
- Error description if any
- Daily schedule : start and stop time
- Off button : set timing to 0
- On button to retrieve last known values (defaulted to 10-17h)
- Cutting edges indicator

Below info can be hidden
- errorCode, statusCode, totalDistance, batteryChargeCycle, rainDelay


## Usefull info

To avoid mowing on a particular day, you can set up a scenario with actions 'off_today' and 'on_today' before midnight to set timings

Avoid fast planning change because complete planning is sent each time but is only recorded after cloud server response. Several messages arriving at the same moment may lead to incorrect time.

Daily schedule - possible actions:
- From on_0 to on_6 where the digits correspond to the current (0 =Sunday)
- off_0 to off_6
- on_today set current day mowing times
- off_today current day mowing time
- set_schedule pour modifier le planning de tonte d'un jour donné. Par défaut l'action n'est pas visible. Le but étant de faire de la planification à l'aide d'un scénario mais il est possible de rendre visible sur le widget si besoin.
Format attendu: numéro jour;heure départ;durée en minutes;bordure Exemples :
1;10:00;120;1 => lundi, démarrage à 10:00 pendant 120 minutes, coupe la bordure
0;08:00;300;0 => dimanche, démarrage à 08:00 pendant 300 minutes, ne coupe pas la bordure

## Widget
- un widget (workAndroid) est disponible sur le market pour un affichage avec des images. Pour cela Il est possible d'utiliser l'info virtualInfo: cocher afficher dans la liste des commandes, sélectionner le widget dans la configuration avancée.
(Recherche équipement pour récupérer l'info d'un autre équipement)

## FAQ

>A quelle fréquence, les données sont-elles réactualisées?

Toutes les 2 minutes si la tondeuse est en activité et toutes les 30 minutes lorsqu'elle est en veille.

>Le plugin m'indique que la tondeuse est coincée mais ce n'est pas le cas, pourquoi?

Cela signifie que la tondeuse était coincée pendant quelques secondes et que le plugin a remonté l'info à ce moment là.
Il est possible de rafraîchir le statut de la tondeuse en utilisant le bouton "refreshValue"

>quels sont les modèles compatibles?
- WG757E
- WG796E.1
- WG797E.1
- WG798E
- WR101SI
- WR102SI
- WR104SI
- WR105SI
- WR106SI
- WR110MI
- WR111MI
- WR112MI
- WG799E
- WR113MI
- WR100SI
- WR101SI.1
- WR102SI.1
- WR103SI
- WR104SI.1
- WR105SI.1
- WR106SI.1
- WR110MI.1
- WR115MI
- WR130E
- WR141E
- WR142E
- WR143E
- WR153E
- WR155E
- WR140
- WR150


Un autre plugin worxLandroid est disponible pour les modèles M/L en version non cloud.

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
- 31: Création des zones de tonte
- 32: Coupe la bordure

>La communication avec la tondeuse est perdue

Il peut arriver par moment de perdre la connexion avec la tondeuse, y compris avec l'application mobile.
Dans certains cas, le fait de réactualiser le code WIFI peut résoudre le problème.
(Faire comme si on voulait ajouter une nouvelle tondeuse sur l'appli mobile, appuyer 3 secondes sur le bouton OK de la tondeuse, renseigner le n° de série et le code WIFI correspondant)
Worx limite volontairement le nombre d'interrogations de l'état de la tondeuse (limite non connue) donc trop de "refresh" pourrait stopper la communication avec le cloud amazon. D'après les tests, il s'agit d'une limite quotidienne.

>Version mosquitto
Les versions mosquitto 1.3.x et antérieures ne sont pas compatibles avec le plugin. Le script d'installation (dépendances) doit pouvoir installer une version plus récente, toutefois il se peut que certaines distributions ne soient pas prises en compte. 
Dans ce cas là, vous pouvez tenter de la mettre à jour manuellement et si possible m'informer afin que je puisse mettre à jour le script d'installation.



## Fonctionnement détaillé

Connexion vers des api worx pour récupérer: les infos utilisateurs, le certificat et les paramètres tondeuses:
https://api.worxlandroid.com:443/api/v2

Connexion au broker Mosquitto en fonction des liens et paramètres récupérés à partir des API précédentes


La clé publique se trouve à ce lien:
https://www.symantec.com/content/en/us/enterprise/verisign/roots/VeriSign-Class%203-Public-Primary-Certification-Authority-G5.pem




## Changelog

[Voir la page dédiée](changelog.md)
