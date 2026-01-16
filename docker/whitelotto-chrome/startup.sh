#!/bin/bash
set -e

#get DOCKERHOST
DOCKERHOST=$(/sbin/ip route|awk '/default/ { print $3 }')
grep -qF -- "$DOCKERHOST\t${MAIN_DOMAIN}" "/etc/hosts" || echo -e "$DOCKERHOST\t${MAIN_DOMAIN}" >> "/etc/hosts"
grep -qF -- "$DOCKERHOST\twww.${MAIN_DOMAIN}" "/etc/hosts" || echo -e "$DOCKERHOST\twww.${MAIN_DOMAIN}" >> "/etc/hosts"
grep -qF -- "$DOCKERHOST\tadmin.${MAIN_DOMAIN}" "/etc/hosts" || echo -e "$DOCKERHOST\tadmin.${MAIN_DOMAIN}" >> "/etc/hosts"
grep -qF -- "$DOCKERHOST\tempire.${MAIN_DOMAIN}" "/etc/hosts" || echo -e "$DOCKERHOST\tempire.${MAIN_DOMAIN}" >> "/etc/hosts"
grep -qF -- "$DOCKERHOST\thq.gginternational.loc" "/etc/hosts" || echo -e "$DOCKERHOST\thq.gginternational.loc" >> "/etc/hosts"

for name in $(echo ${WHITELABEL_DOMAINS} | tr "," "\n")
do
	grep -qF -- "$DOCKERHOST\t${name}" "/etc/hosts" || echo -e "$DOCKERHOST\t${name}" >> "/etc/hosts"
	grep -qF -- "$DOCKERHOST\twww.${name}" "/etc/hosts" || echo -e "$DOCKERHOST\twww.${name}" >> "/etc/hosts"
	grep -qF -- "$DOCKERHOST\tmanager.${name}" "/etc/hosts" || echo -e "$DOCKERHOST\tmanager.${name}" >> "/etc/hosts"
	grep -qF -- "$DOCKERHOST\taff.${name}" "/etc/hosts" || echo -e "$DOCKERHOST\taff.${name}" >> "/etc/hosts"
	grep -qF -- "$DOCKERHOST\tapi.${name}" "/etc/hosts" || echo -e "$DOCKERHOST\tapi.${name}" >> "/etc/hosts"
done

exec "/opt/bin/entry_point.sh"