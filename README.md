# Projet Infra
### Pougeard-Dulimbert Arthur, COUTANT Mathias, Lopez Théo, Magne Eric

## Sommaire 
    - [Sommaire] (#sommaire)
    - [Lien] (#lien)
    - [Machine 1 : apache] (#machine-1-apache)
    - [Machine 2 : Le Proxy] (#machine-2-:-le-proxy)
    - [WAF] (#waf)
    - [Machine 3 : Backups] (#machine-3-:-backups)

## Lien
Le script de backup :
- [Le Script](backup.sh)

Le service : 
- [Le Service](backup.service)

Le Timer :
- [Le timer](backup.timer)

## Machine 1 : Apache


Installations requises : 

```
sudo dnf install httpd wget git firewalld nano -y
sudo systemctl start httpd
sudo systemctl enable httpd
sudo systemctl start firewalld 
sudo systemctl enable httpd
sudo firewall-cmd --zone=public --add-port=80/tcp --permanent
sudo firewall-cmd --reload
```

Pouvoir accéder au site web :

```
sudo git clone https://github.com/EricMgn/projet-infra
sudo mv projet-infra /var/www/
sudo chown apache:apache /var/www/projet-infra
sudo chmod 755 /var/www/projet-infra/
```

La configuration de Apache :

```
[rocky@ip-172-31-37-154 ~]$ cat /etc/httpd/conf.d/httpd.conf
<VirtualHost *:80>
  # on indique le chemin de notre webroot
  DocumentRoot /var/www/php_exam/
  # on précise le nom que saisissent les clients pour accéder au service
  ServerName  projet-infra

  # on définit des règles d'accès sur notre webroot
  <Directory /var/www/php_exam>
    Require all granted
    AllowOverride All
    Options FollowSymLinks MultiViews
    <IfModule mod_dav.c>
      Dav off
    </IfModule>
  </Directory>
</VirtualHost>
```

Installation de php :

ATTENTION : INSTALLER PHP DANS LA MÊME VERSION QUE CELLE DU SITE :

```
sudo dnf install epel-release
sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
sudo dnf module install php:remi-7.4
```

Pour vérifier il est possible de faire la commande suivante :

```
php -v
```
On restart apache :

```
sudo systemctl restart httpd
sudo systemctl status httpd
```

On doit pouvoir joindre notre site grâce à l'ip publique de notre machine si vous l'hébergez depuis un server.

## Machine 2 : Le Proxy 

```
sudo dnf install nginx nano wget firewalld 
sudo systemctl start firewalld
sudo systemctl enable firewalld
sudo systemctl start nginx
sudo systemctl enable nginx
sudo firewall-cmd --zone=public --add-port=80/tcp --permanent
sudo firewall-cmd --reload
```

Certificats SSL :

```
openssl req -new -newkey rsa:2048 -days 365 -nodes -x509 -keyout server.key -out server.crt
sudo mv server.crt /etc/pki/tls/certs/
sudo mv server.key /etc/pki/tls/private/
```

Conf de Nginx :

```
[rocky@ip-172-31-19-26 ~]$ cat /etc/nginx/nginx.conf
# For more information on configuration, see:
#   * Official English Documentation: http://nginx.org/en/docs/
#   * Official Russian Documentation: http://nginx.org/ru/docs/

user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log;
pid /run/nginx.pid;

# Load dynamic modules. See /usr/share/doc/nginx/README.dynamic.
include /usr/share/nginx/modules/*.conf;

events {
    worker_connections 1024;
}

http {
    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    access_log  /var/log/nginx/access.log  main;

    sendfile            on;
    tcp_nopush          on;
    tcp_nodelay         on;
    keepalive_timeout   65;
    types_hash_max_size 4096;

    include             /etc/nginx/mime.types;
    default_type        application/octet-stream;

    # Load modular configuration files from the /etc/nginx/conf.d directory.
    # See http://nginx.org/en/docs/ngx_core_module.html#include
    # for more information.
    include /etc/nginx/conf.d/*.conf;

    server {
        listen       80;
        listen       [::]:80;
        server_name  _;
        root         /usr/share/nginx/html;

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        error_page 404 /404.html;
        location = /404.html {
        }

        error_page 500 502 503 504 /50x.html;
        location = /50x.html {
        }
    }

# Settings for a TLS enabled server.
#
#    server {
#        listen       443 ssl http2;
#        listen       [::]:443 ssl http2;
#        server_name  _;
#        root         /usr/share/nginx/html;
#
#        ssl_certificate "/etc/pki/nginx/server.crt";
#        ssl_certificate_key "/etc/pki/nginx/private/server.key";
#        ssl_session_cache shared:SSL:1m;
#        ssl_session_timeout  10m;
#        ssl_ciphers PROFILE=SYSTEM;
#        ssl_prefer_server_ciphers on;
#
#        # Load configuration files for the default server block.
#        include /etc/nginx/default.d/*.conf;
#
#        error_page 404 /404.html;
#            location = /40x.html {
#        }
#
#        error_page 500 502 503 504 /50x.html;
#            location = /50x.html {
#        }
#    }

}
```

Restart de Nginx :

```
sudo systemctl restart nginx 
sudo systemctl status nginx
```

## Le WAF 


Installation :

```
sudo dnf install nginx-plus-module-modsecurity
sudo systemctl start nginx
sudo systemctl enable nginx
dnf group install 'Development Tools'
```

Il faut maintenant se créer un compte sur Comodo :
https://waf.comodo.com/

Il nous faut maintenant télécharger manuellement les règles du WAF :
https://waf.comodo.com/
télécharger les dernières règles de Comodo depuis votre machine et la SCP jusqu'à votre server apache :

```
scp -i "Php_Server.pem" .\cwaf_rules-1.240.tgz rocky@ec2-52-47-186-64.eu-west-3.compute.amazonaws.com:/home/rocky
sudo mv /home/rpcky/cwaf_rules-1.240.tgz /usr/local/cwaf/rules/
cd /usr/local/cwaf/rules/
tar xzvf cwaf_rules-1.240.tgz
```

Installation de Comodo :

```
wget https://waf.comodo.com/cpanel/cwaf_client_install.sh
bash cwaf_client_install.sh
No web host management panel found, continue in 'standalone' mode? [y/n]: y
Some required perl modules are missed. Install them? This can take a while. [y/n]: y
Enter CWAF login: username@domain.com
Enter password for 'username@domain.com' (will not be shown): *************************
Confirm password for 'username@domain.com' (will not be shown): ************************
Enter absolute CWAF installation path prefix (/cwaf will be appended): /usr/local
Install into '/usr/local/cwaf' ? [y/n]: y
If you have non-standard Apache/nginx config path enter it here:
Do you want to use HTTP GUI to manage CWAF rules? [y/n]: n
Do you want to protect your server with default rule set? [y/n]: y
```
Il faut rentrer l'adresse mail et le mot de passe utilisé qu'on à utilisé pour s'inscrire sur Comodo.


Configurer le CWAF :

```
sudo nano /etc/httpd/conf.d/mod_security.conf
```

Ajouter cette ligne en haut du fichier :

```
<IfModule mod_security2.c>
    # Default recommended configuration
    SecRuleEngine On
    SecStatusEngine On
```

Rendez vous ensuite en bas du fichier de conf pour ajouter une ligne qui ajoute ajoute les règles téléchargées plus tôt :

```
    # ModSecurity Core Rules Set and Local configuration
    IncludeOptional modsecurity.d/*.conf
    IncludeOptional modsecurity.d/activated_rules/*.conf
    IncludeOptional modsecurity.d/local_rules/*.conf
    Include "/usr/local/cwaf/etc/cwaf.conf"
</IfModule>
```
On redémarre tous les services 

```
sudo systemctl restart httpd
sudo systemctl restart nginx
```

Si tout redémarre correctement le CWAF est fonctionnel.

Fail2ban :

Installation :

```
sudo dnf install epel-release -y
sudo dnf install fail2ban fail2ban-firewalld -y
sudo systemctl start fail2ban
sudo systemctl enable fail2ban
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo cat /etc/fail2ban/jail.d/sshd.local | tail -n 7
[sshd]
enabled = true
bantime = 1d
maxretry = 3
sudo mv /etc/fail2ban/jail.d/00-firewalld.conf /etc/fail2ban/jail.d/00-firewalld.local
sudo systemctl restart fail2ban
``` 

## Machine 3 : Backups

Machine pour les backups :

```
sudo mkdir backups
sudo mkdir /backups/mon_site
```

Installer NFS :

```
sudo dnf install nfs-utils -y
cat /etc/exports
/backups/music    client_ip(rw,sync,no_root_squash,no_subtree_check)
```

Adaptation des règles du firewall :

```
sudo firewall-cmd --permanent --add-service=nfs
sudo firewall-cmd --permanent --add-service=mountd
sudo firewall-cmd --reload
```

Machine Apache : 

Création du répertoire :

```
sudo mkdir backup
```

Installation de NFS :

```
sudo dnf install nfs-utils -y
sudo mount -t nfs 52.47.186.64:backups/mon_site/ backup/
```
Le script de backup :
- [Le Script](backup.sh)

Le service : 
- [Le Service](backup.service)


Création du user qui gère le service :

```
sudo useradd backup
sudo chown backup backup/
sudo chmod 750 backup/
sudo systemctl daemon-reload
sudo systemctl start backup
sudo systemctl enable backup
```

- [Le timer](backup.timer)