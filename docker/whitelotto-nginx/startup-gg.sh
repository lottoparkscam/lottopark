#!/bin/bash
set -e

#get DOCKERHOST
DOCKERHOST=$(/sbin/ip route|awk '/default/ { print $3 }')
sed -i 's/DOCKERHOST/'$DOCKERHOST'/gm' /etc/nginx/nginx.conf

ln -sf /dev/stdout /var/log/nginx/access.log
ln -sf /dev/stderr /var/log/nginx/error.log

if [ $ENVIRONMENT != "development" ]; then
  i=1
  name=${MAIN_DOMAIN}
  str=$(printf "%03d" "$i")
  if [ ! -f /etc/nginx/sites-available/$str-$name ]; then
	  cp /etc/nginx/sites-available/XXX-main-template /etc/nginx/sites-available/$str-$name
	  sed -i 's/MAINDOMAIN/'$name'/gm' /etc/nginx/sites-available/$str-$name
	  cp /etc/nginx/conf.d/headers-security-domain /etc/nginx/conf.d/headers-security-$name
	  sed -i 's/MAINDOMAIN/'$name'/gm' /etc/nginx/conf.d/headers-security-$name
	  ln -sf /etc/nginx/sites-available/$str-$name /etc/nginx/sites-enabled/
	  ln -sf /dev/stdout /var/log/nginx/$name-access.log
	  ln -sf /dev/stderr /var/log/nginx/$name-error.log
	  mkdir -p /var/cache/nginx/$name

	  i=2
	  for name in $(echo ${WHITELABEL_DOMAINS} | tr "," "\n")
	  do
	    echo "Enabling site" $name
	    str=$(printf "%03d" "$i")
	    cp /etc/nginx/sites-available/XXX-whitelabel-template /etc/nginx/sites-available/$str-$name
	    sed -i 's/WHITELABEL/'$name'/gm' /etc/nginx/sites-available/$str-$name
	    cp /etc/nginx/conf.d/headers-security-domain /etc/nginx/conf.d/headers-security-$name
	    sed -i 's/MAINDOMAIN/'$name'/gm' /etc/nginx/conf.d/headers-security-$name
	    ln -sf /etc/nginx/sites-available/$str-$name /etc/nginx/sites-enabled
	    ln -sf /dev/stdout /var/log/nginx/$name-access.log
	    ln -sf /dev/stderr /var/log/nginx/$name-error.log
	    i=$((i+1))
	  done

	  rm /etc/nginx/sites-available/XXX-whitelabel-template
	  rm /etc/nginx/sites-available/XXX-main-template
	  
	  mkdir -p /var/cache/nginx/$name
  fi
else
	mkdir -p /var/cache/nginx/whitelotto.loc
	mkdir -p /var/cache/nginx/lottopark.loc
	ln -sf /dev/stdout /var/log/nginx/whitelotto.loc-access.log
	ln -sf /dev/stderr /var/log/nginx/whitelotto.loc-error.log
	ln -sf /dev/stdout /var/log/nginx/lottopark.loc-access.log
	ln -sf /dev/stderr /var/log/nginx/lottopark.loc-error.log
fi

exec startup.sh "$@" 
