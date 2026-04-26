#!/usr/bin/env bash
set -euo pipefail

# OpenTogG – Apache VirtualHost setup for testing server
# Run this once on the server: sudo bash setup-apache.sh

SITE_NAME="opentogg"
DOC_ROOT="/var/www/html/opentogg/public"
PORT=8082

# Add Listen directive if not already present
if ! grep -q "Listen ${PORT}" /etc/apache2/ports.conf; then
    echo "Listen ${PORT}" >> /etc/apache2/ports.conf
    echo "Added Listen ${PORT} to ports.conf"
fi

# Create VirtualHost config
cat > /etc/apache2/sites-available/${SITE_NAME}.conf <<EOF
<VirtualHost *:${PORT}>
    ServerName opentogg.roblab.app
    DocumentRoot ${DOC_ROOT}

    <Directory ${DOC_ROOT}>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${SITE_NAME}-error.log
    CustomLog \${APACHE_LOG_DIR}/${SITE_NAME}-access.log combined
</VirtualHost>
EOF

echo "Created VirtualHost config: /etc/apache2/sites-available/${SITE_NAME}.conf"

# Enable mod_rewrite and the site
a2enmod rewrite
a2ensite ${SITE_NAME}

# Reload Apache
systemctl reload apache2

echo "Apache configured and reloaded. ${SITE_NAME} is live on port ${PORT}."
