# Variables
file="Emby_$(date +"%y%m%d_%H%m%S")"
filepath="backup/${file}"
conf="/var/lib/emby/config/"

#Création du fichier de backup
/usr/bin/tar -czf "${filepath}.tar.gz" -C "${conf}" config data themes