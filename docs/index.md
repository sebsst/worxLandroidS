# Robots tondeuses Worx Landroid modèles S

## Présentation

Ce plugin permet de se connecter aux tondeuses worx Landroid modèles S.

### Configuration du plugin

La connexion vers la tondeuse se fait à partir d'un serveur cloud en utilisant le compte utilisé lors de l'enregistrement de la tondeuse.

Les identifiants correspondent à ceux de l'application mobile.
Vous devez attendre la fin de l'activation des dépendances pour permettre la communication par le protocole Mosquitto.

Une fois la sauvegarde effectuée, un nouvel équipement tondeuse va être créé automatiquement. En cas de soucis vous pouvez réactualiser les informations du cloud en utilisant la case à cocher correspondante et en effectuant une sauvegarde.

L'arrêt du daemon permet de stopper la connexion avec la tondeuse.



## FAQ

>Est-ce que je peux démarrer ou arrêter la tondeuse à partir du plugin? 

Non, pour le moment le plugin permet de récupérer les informations de la tondeuse.
Dans une prochaine version du plugin, il sera également possible de piloter la tondeuse.

>quels sont les modèles compatibles?

Le plugin est compatible avec les modèle S worx Landroid WR10xSx. 
Un autre plugin worxLandroid est disponible pour les modèles M.


## Changelog

[Voir la page dédiée](changelog.md)
