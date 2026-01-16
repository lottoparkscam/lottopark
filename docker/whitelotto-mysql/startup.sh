#!/bin/bash

echo "[i] Setting up new user credentials."
service mysql start
i=0
while ! mysqladmin --defaults-file=/etc/mysql/debian.cnf ping -h"localhost" proc --silent; do
    sleep 1
    echo "[$i] Waiting for MySQL to start..."
    ((i=i+1))
done

echo "[i] Setting root password."
mysql --defaults-file=/etc/mysql/debian.cnf -e "DROP USER 'root'@'localhost'; CREATE USER 'root'@'%' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD'; GRANT ALL PRIVILEGES ON *.* TO 'root'@'%'WITH GRANT OPTION; FLUSH PRIVILEGES;"

echo "[i] Creating .my.cnf"
sed -i 's/MYSQL_ROOT_PASSWORD/'$MYSQL_ROOT_PASSWORD'/g' /root/.my.cnf

# WORDPRESS
echo "[i] Creating new DATABASE: $MYSQL_WORDPRESS_USER"
mysql -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_WORDPRESS_DB\`;"

echo "[i] Create new USER: $MYSQL_WORDPRESS_USER for new database $MYSQL_WORDPRESS_DB."
mysql -e "CREATE USER \`$MYSQL_WORDPRESS_USER\`@'%' IDENTIFIED BY '$MYSQL_WORDPRESS_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON \`$MYSQL_WORDPRESS_USER\`.* TO '$MYSQL_WORDPRESS_USER'@'%';"

# PLATFORM
echo "[i] Creating new DATABASE: $MYSQL_PLATFORM_USER"
mysql -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_PLATFORM_DB\`;"

echo "[i] Create new USER: $MYSQL_PLATFORM_USER for new database $MYSQL_PLATFORM_DB."
mysql -e "CREATE USER \`$MYSQL_PLATFORM_USER\`@'%' IDENTIFIED BY '$MYSQL_PLATFORM_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON \`$MYSQL_PLATFORM_USER\`.* TO '$MYSQL_PLATFORM_USER'@'%';"

# SESSIONS
echo "[i] Creating new DATABASE: $MYSQL_SESSIONS_USER"
mysql -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_SESSIONS_DB\`;"

echo "[i] Create new USER: $MYSQL_SESSIONS_USER for new database $MYSQL_SESSIONS_DB."
mysql -e "CREATE USER \`$MYSQL_SESSIONS_USER\`@'%' IDENTIFIED BY '$MYSQL_SESSIONS_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON \`$MYSQL_SESSIONS_USER\`.* TO '$MYSQL_SESSIONS_USER'@'%';"

#Necessary for granting privileges below
mysql -e "FLUSH PRIVILEGES;"

echo "[i] Importing databases"
for f in /docker-entrypoint-initdb.d/*; do
	case "$f" in
		*.sql) echo "[Entrypoint] running $f"; mysql < "$f" ;;
	esac
	echo
done

echo "[i] CREATE and GRANT PRIVILEGES for PHPMYADMIN user."
mysql -e "CREATE DATABASE phpmyadmin;"
mysql -e "CREATE USER 'phpmyadmin'@'%' IDENTIFIED BY '$MYSQL_PHPMYADMIN_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON \`phpmyadmin\`.* TO 'phpmyadmin'@'%';"

service mysql stop

exec gosu mysql "$@"
