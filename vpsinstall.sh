#!/bin/bash

# Zaustavi skriptu ako dođe do greške
set -e

# Provjera je li skripta pokrenuta kao root
if [ "$EUID" -ne 0 ]; then
  echo "❌ Ova skripta mora biti pokrenuta kao root (koristite sudo)."
  exit 1
fi

echo "🚀 Započinjem potpuno automatizirano postavljanje Ubuntu 24.04 servera..."

# ==========================================
# ISKLJUČIVANJE INTERAKCIJE
# ==========================================
export DEBIAN_FRONTEND=noninteractive
export NEEDRESTART_MODE=a
export NEEDRESTART_SUSPEND=1

APT_FLAGS="-y -q -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confold"

# ==========================================
# KONFIGURACIJSKE VARIJABLE
# ==========================================
REPO_URL="https://github.com/tjakopec/phpmeetup.git"
REPO_DIR="/var/www/phpmeetup_repo"
SYMFONY_DIR="${REPO_DIR}/phpmeetupHuman"

DOMAIN="unixoidi.pro"
ADMIN_EMAIL="tjakopec@gmail.com" 

DB_NAME="symfony_db"
DB_USER="symfony_user"
DB_PASS=$(openssl rand -hex 16)
APP_SECRET=$(openssl rand -hex 24)

# ==========================================
# 1. AŽURIRANJE I DODAVANJE PHP REPOZITORIJA
# ==========================================
echo "📦 Ažuriranje sustava i dodavanje PPA za PHP 8.5..."
apt-get update
apt-get upgrade $APT_FLAGS

apt-get install $APT_FLAGS software-properties-common curl git unzip acl mariadb-server \
    nginx certbot python3-certbot-nginx ufw

LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
apt-get update

# ==========================================
# 2. INSTALACIJA PHP 8.5 I EKSTENZIJA
# ==========================================
echo "🐘 Instalacija PHP 8.5 i potrebnih ekstenzija..."
apt-get install $APT_FLAGS php8.5-cli php8.5-fpm php8.5-mysql php8.5-xml \
    php8.5-mbstring php8.5-curl php8.5-intl php8.5-zip php8.5-bcmath

update-alternatives --set php /usr/bin/php8.5

# ==========================================
# 3. INSTALACIJA COMPOSERA
# ==========================================
if ! command -v composer &> /dev/null; then
    echo "🎼 Instalacija Composera..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# ==========================================
# 4. POSTAVLJANJE MARIADB BAZE PODATAKA
# ==========================================
echo "🗄️ Postavljanje MariaDB baze i korisnika..."
systemctl start mariadb
systemctl enable mariadb

mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# ==========================================
# 5. KLONIRANJE REPOZITORIJA
# ==========================================
echo "📥 Kloniranje Git repozitorija..."
mkdir -p /var/www

if [ -d "$REPO_DIR" ]; then
    echo "⚠️ Direktorij već postoji. Brišem staru verziju..."
    rm -rf "$REPO_DIR"
fi

git clone "$REPO_URL" "$REPO_DIR"
cd "$SYMFONY_DIR"

# ==========================================
# 6. GENERIRANJE .env DATOTEKE
# ==========================================
echo "⚙️ Kreiranje .env datoteke sa svježim podacima..."

cat <<EOF > .env
APP_ENV=dev
APP_SECRET=${APP_SECRET}
DATABASE_URL="mysql://${DB_USER}:${DB_PASS}@127.0.0.1:3306/${DB_NAME}?serverVersion=10.11.8-MariaDB&charset=utf8mb4"
EOF

# ==========================================
# 7. INSTALACIJA OVISNOSTI I INICIJALIZACIJA BAZE
# ==========================================
echo "📚 Pokretanje composer install..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --optimize-autoloader --no-interaction

echo "🧹 Brisanje Symfony cache-a..."
php bin/console cache:clear

echo "🏗️ Generiranje i izvođenje migracija..."
php bin/console make:migration --no-interaction || true
php bin/console doctrine:migrations:migrate --no-interaction

echo "🌱 Učitavanje testnih podataka (Fixtures)..."
php bin/console doctrine:fixtures:load --group=all --no-interaction

# ==========================================
# 8. POSTAVLJANJE DOZVOLA (PERMISSIONS)
# ==========================================
echo "🔒 Postavljanje dozvola..."
chown -R www-data:www-data "$REPO_DIR"

HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var

# ==========================================
# 9. POSTAVLJANJE NGINX VHOSTA
# ==========================================
echo "🌐 Postavljanje Nginx virtualnog hosta za ${DOMAIN}..."

cat <<EOF > /etc/nginx/sites-available/${DOMAIN}
server {
    server_name ${DOMAIN} www.${DOMAIN};
    root ${SYMFONY_DIR}/public;

    location / {
        try_files \$uri /index.php\$is_args\$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/${DOMAIN}_error.log;
    access_log /var/log/nginx/${DOMAIN}_access.log;
}
EOF

ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/${DOMAIN}
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

# ==========================================
# 10. GENERIRANJE SSL CERTIFIKATA (CERTBOT)
# ==========================================
echo "🔐 Generiranje SSL certifikata s Certbotom..."
certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${ADMIN_EMAIL}" --redirect

# ==========================================
# 11. POSTAVLJANJE VATROZIDA (UFW)
# ==========================================
echo "🛡️ Konfiguriranje UFW vatrozida..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

echo "====================================================="
echo "✅ POSTAVLJANJE JE USPJEŠNO ZAVRŠENO!"
echo "====================================================="
echo "Web adresa:          https://${DOMAIN}"
echo "Glavni repozitorij:  $REPO_DIR"
echo "Symfony aplikacija:  $SYMFONY_DIR"
echo "Baza podataka:       $DB_NAME"
echo "Korisnik baze:       $DB_USER"
echo "Lozinka baze:        $DB_PASS"
echo "====================================================="