# worxLandroidS

Robots tondeuses Worx Landroid modèles S

# Présentation
Ce plugin permet de se connecter aux tondeuses worx Landroid modèles S.

# Configuration du plugin

La connexion vers la tondeuse se fait à partir d'un serveur cloud en utilisant le compte utilisé lors de l'enregistrement de la tondeuse.

Les identifiants correspondent à ceux de l'application mobile. Vous devez attendre la fin de l'activation des dépendances pour permettre la communication avec la tondeuse par le protocole Mosquitto.

Une fois la sauvegarde effectuée, un nouvel équipement tondeuse va être créé automatiquement. En cas de soucis vous pouvez réactualiser les informations du cloud en utilisant la case à cocher correspondante et en effectuant une nouvelle sauvegarde.

L'arrêt du daemon permet de stopper la connexion avec la tondeuse. En cas du défaillance il se peut que le Daemon soit à l'arrêt et vous pouvez tenter un redémarrage.

# Utilisation

Le nom par défaut = LandroidS+adress Mac de la tondeuse

Le dashboard affiche:

-la date et heure de la dernière communication
-Etat de la tondeuse avec le code correspondant
-Description de l'erreur avec le code correspondant
-Le planning par jour avec l'heure de démarrage et d'arrêt
-La croix permet de mettre les horaires à 0
-L'autre bouton permet de récupérer les derniers horaires communiqués au plugin (10H - 17h par défaut)
-Edge signifie la coupe des bordures est planifié

# FAQ

>quels sont les modèles compatibles?

Le plugin est compatible avec les modèle S worx Landroid WR10xSx. Un autre plugin worxLandroid est disponible pour les modèles M.
