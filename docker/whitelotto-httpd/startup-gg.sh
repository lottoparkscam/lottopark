#!/bin/bash
set -e

sed -i 's#^Listen 80$#Listen 8080#g' /etc/apache2/ports.conf
sed -i 's#\tListen 443#\tListen 8443#gm' /etc/apache2/ports.conf

ln -sf /dev/stdout /var/log/apache2/access.log
ln -sf /dev/stderr /var/log/apache2/error.log

if [ $ENVIRONMENT != "development" ]
then
  i=1
  name=${MAIN_DOMAIN}
  str=$(printf "%03d" "$i")
  if [ ! -f /etc/apache2/sites-available/$str-$name.conf ]
  then
	  cp /etc/apache2/sites-available/XXX-main-template.conf /etc/apache2/sites-available/$str-$name.conf
	  sed -i 's/MAINDOMAIN/'$name'/gm' /etc/apache2/sites-available/$str-$name.conf
	  a2ensite $str-$name

	  i=2
	  for name in $(echo ${WHITELABEL_DOMAINS} | tr "," "\n")
	  do
	    echo "Enabling site" $name
	    str=$(printf "%03d" "$i")
	    cp /etc/apache2/sites-available/XXX-whitelabel-template.conf /etc/apache2/sites-available/$str-$name.conf
	    sed -i 's/WHITELABEL/'$name'/gm' /etc/apache2/sites-available/$str-$name.conf
	    ln -sf /dev/stdout /var/log/apache2/$name-access.log
	    ln -sf /dev/stderr /var/log/apache2/$name-error.log
	    a2ensite $str-$name
	    i=$((i+1))
	  done

	  rm /etc/apache2/sites-available/XXX-whitelabel-template.conf
	  rm /etc/apache2/sites-available/XXX-main-template.conf
  fi
fi

exec startup.sh "$@"
