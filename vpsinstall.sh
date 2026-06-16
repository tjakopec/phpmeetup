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
# POSTAVKE ZA LOGIRANJE I GREŠKE
# ==========================================
LOG_FILE="/var/log/phpmeetup_install.log"
> "$LOG_FILE" # Čisti log datoteku pri svakom novom pokretanju

# Funkcija koja se poziva ako bilo koja naredba baci grešku
error_handler() {
    echo -e "\n\n❌ Došlo je do greške! Skripta je zaustavljena."
    echo "Detalje o grešci možete pronaći u log datoteci: $LOG_FILE"
    exit 1
}
trap 'error_handler' ERR

# ==========================================
# PRIKUPLJANJE PODATAKA OD KORISNIKA
# ==========================================
echo "Molimo unesite sljedeće podatke (pritisnite Enter za zadane vrijednosti):"
echo "------------------------------------------------------------------------"

read -p "REPO_URL [https://github.com/tjakopec/phpmeetup.git]: " input_repo_url
REPO_URL=${input_repo_url:-https://github.com/tjakopec/phpmeetup.git}

read -p "REPO_DIR [/var/www/phpmeetup]: " input_repo_dir
REPO_DIR=${input_repo_dir:-/var/www/phpmeetup}

read -p "DOMAIN [phpmeetupos.space]: " input_domain
DOMAIN=${input_domain:-phpmeetupos.space}

read -p "ADMIN_EMAIL [tjakopec@gmail.com]: " input_admin_email
ADMIN_EMAIL=${input_admin_email:-tjakopec@gmail.com}

echo "------------------------------------------------------------------------"
echo "⏳ Započinjem instalaciju. Sav izlaz bilježi se u: $LOG_FILE"
echo "------------------------------------------------------------------------"

# ==========================================
# OSTALE KONFIGURACIJSKE VARIJABLE
# ==========================================
SYMFONY_DIR="${REPO_DIR}/phpmeetupHuman"
DB_NAME="symfony_db"
DB_USER="symfony_user"
DB_PASS=$(openssl rand -hex 16)
APP_SECRET=$(openssl rand -hex 24)

# Isključivanje interakcije za apt-get
export DEBIAN_FRONTEND=noninteractive
export NEEDRESTART_MODE=a
export NEEDRESTART_SUSPEND=1
APT_FLAGS="-y -q -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confold"

# ==========================================
# FUNKCIJA ZA PROGRESS BAR
# ==========================================
TOTAL_STEPS=12
CURRENT_STEP=0

show_progress() {
    CURRENT_STEP=$((CURRENT_STEP + 1))
    local message="$1"
    local percent=$((CURRENT_STEP * 100 / TOTAL_STEPS))
    local filled=$((CURRENT_STEP * 40 / TOTAL_STEPS))
    local empty=$((40 - filled))
    
    # Generiranje ispunjenog i praznog dijela bara
    local bar_filled=$(printf "%${filled}s" | tr ' ' '█')
    local bar_empty=$(printf "%${empty}s" | tr ' ' '░')
    
    # \r vraća kursor na početak linije, \033[K briše ostatak linije
    printf "\r\033[K[%s%s] %3d%% | %s" "$bar_filled" "$bar_empty" "$percent" "$message"
}

# ==========================================
# 1. AŽURIRANJE I DODAVANJE PHP REPOZITORIJA
# ==========================================
show_progress "Ažuriranje OS-a i dodavanje repozitorija..."
apt-get update >> "$LOG_FILE" 2>&1
apt-get upgrade $APT_FLAGS >> "$LOG_FILE" 2>&1
apt-get install $APT_FLAGS software-properties-common curl git unzip acl mariadb-server nginx certbot python3-certbot-nginx ufw >> "$LOG_FILE" 2>&1
LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php >> "$LOG_FILE" 2>&1
apt-get update >> "$LOG_FILE" 2>&1

# ==========================================
# 2. INSTALACIJA PHP 8.5 I EKSTENZIJA
# ==========================================
show_progress "Instalacija PHP 8.5 i ekstenzija..."
apt-get install $APT_FLAGS php8.5-cli php8.5-fpm php8.5-mysql php8.5-xml \
    php8.5-mbstring php8.5-curl php8.5-intl php8.5-zip php8.5-bcmath >> "$LOG_FILE" 2>&1
update-alternatives --set php /usr/bin/php8.5 >> "$LOG_FILE" 2>&1

# ==========================================
# 3. INSTALACIJA COMPOSERA
# ==========================================
show_progress "Instalacija Composera..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer >> "$LOG_FILE" 2>&1
fi

# ==========================================
# 4. POSTAVLJANJE MARIADB BAZE PODATAKA
# ==========================================
show_progress "Konfiguracija MariaDB baze..."
systemctl start mariadb >> "$LOG_FILE" 2>&1
systemctl enable mariadb >> "$LOG_FILE" 2>&1
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" >> "$LOG_FILE" 2>&1
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';" >> "$LOG_FILE" 2>&1
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';" >> "$LOG_FILE" 2>&1
mysql -e "FLUSH PRIVILEGES;" >> "$LOG_FILE" 2>&1

# ==========================================
# 5. KLONIRANJE REPOZITORIJA
# ==========================================
show_progress "Kloniranje Git repozitorija..."
mkdir -p /var/www >> "$LOG_FILE" 2>&1
if [ -d "$REPO_DIR" ]; then
    rm -rf "$REPO_DIR" >> "$LOG_FILE" 2>&1
fi
git clone "$REPO_URL" "$REPO_DIR" >> "$LOG_FILE" 2>&1
cd "$SYMFONY_DIR"

# ==========================================
# 6. GENERIRANJE .env DATOTEKE
# ==========================================
show_progress "Generiranje .env datoteke..."
cat <<EOF > .env
APP_ENV=dev
APP_SECRET=${APP_SECRET}
DEFAULT_URI=https://${DOMAIN}
DATABASE_URL="mysql://${DB_USER}:${DB_PASS}@127.0.0.1:3306/${DB_NAME}?serverVersion=10.11.8-MariaDB&charset=utf8mb4"
EOF

# ==========================================
# 7. INSTALACIJA SYMFONY OVISNOSTI
# ==========================================
show_progress "Preuzimanje Composer paketa..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --optimize-autoloader --no-interaction >> "$LOG_FILE" 2>&1

# ==========================================
# 8. INICIJALIZACIJA BAZE (MIGRACIJE I FIXTURES)
# ==========================================
show_progress "Postavljanje strukture baze i testnih podataka..."
php bin/console cache:clear >> "$LOG_FILE" 2>&1
php bin/console doctrine:migrations:migrate --no-interaction >> "$LOG_FILE" 2>&1
php bin/console doctrine:fixtures:load --group=all --no-interaction >> "$LOG_FILE" 2>&1

# ==========================================
# 9. POSTAVLJANJE DOZVOLA
# ==========================================
show_progress "Podešavanje sigurnosnih dozvola..."
chown -R www-data:www-data "$REPO_DIR" >> "$LOG_FILE" 2>&1
HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var >> "$LOG_FILE" 2>&1
setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var >> "$LOG_FILE" 2>&1

# ==========================================
# 10. POSTAVLJANJE NGINX VHOSTA
# ==========================================
show_progress "Konfiguracija Nginx web servera..."
cat <<EOF > /etc/nginx/sites-available/${DOMAIN}
server {
    server_name ${DOMAIN} www.${DOMAIN};
    root ${SYMFONY_DIR}/public;
    location / { try_files \$uri /index.php\$is_args\$args; }
    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        internal;
    }
    location ~ \.php$ { return 404; }
    error_log /var/log/nginx/${DOMAIN}_error.log;
    access_log /var/log/nginx/${DOMAIN}_access.log;
}
EOF
ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/${DOMAIN} >> "$LOG_FILE" 2>&1
rm -f /etc/nginx/sites-enabled/default >> "$LOG_FILE" 2>&1
systemctl restart nginx >> "$LOG_FILE" 2>&1

# ==========================================
# 11. GENERIRANJE SSL CERTIFIKATA (CERTBOT)
# ==========================================
show_progress "Izdavanje besplatnog SSL certifikata..."
certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${ADMIN_EMAIL}" --redirect >> "$LOG_FILE" 2>&1

# ==========================================
# 12. POSTAVLJANJE VATROZIDA (UFW)
# ==========================================
show_progress "Paljenje i podešavanje vatrozida..."
ufw allow 22/tcp >> "$LOG_FILE" 2>&1
ufw allow 80/tcp >> "$LOG_FILE" 2>&1
ufw allow 443/tcp >> "$LOG_FILE" 2>&1
ufw --force enable >> "$LOG_FILE" 2>&1

# Prijelom linije kako završna poruka ne bi pregazila progress bar
echo -e "\n"

echo "====================================================="
echo "✅ POSTAVLJANJE JE USPJEŠNO ZAVRŠENO!"
echo "====================================================="
echo "URL:                 https://${DOMAIN}"
echo "Glavni repozitorij:  $REPO_DIR"
echo "Symfony aplikacija:  $SYMFONY_DIR"
echo "Baza podataka:       $DB_NAME"
echo "Korisnik baze:       $DB_USER"
echo "Lozinka baze:        $DB_PASS"
echo "====================================================="