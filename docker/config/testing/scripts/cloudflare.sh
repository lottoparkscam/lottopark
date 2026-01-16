#!/bin/bash

CF_EMAIL=$2
CF_AUTH_KEY=$3
CF_WHITELOTTO_WORK_ZONE_ID=$4
CF_LOTTOPARK_WORK_ZONE_ID=$5
SPATH='/usr/local/devops-tools/cicd/review-apps/whitelotto'
WHITELOTTO_IDENTIFIER=`${SPATH}/identifier.sh register ${6}`

WHITELOTTO_DOMAINS=("${WHITELOTTO_IDENTIFIER}" "www.${WHITELOTTO_IDENTIFIER}" "empire.${WHITELOTTO_IDENTIFIER}" "admin.${WHITELOTTO_IDENTIFIER}" "mailhog.${WHITELOTTO_IDENTIFIER}")
LOTTOPARK_DOMAINS=("${WHITELOTTO_IDENTIFIER}" "www.${WHITELOTTO_IDENTIFIER}" "manager.${WHITELOTTO_IDENTIFIER}" "aff.${WHITELOTTO_IDENTIFIER}" "api.${WHITELOTTO_IDENTIFIER}" "content.${WHITELOTTO_IDENTIFIER}" "casino.${WHITELOTTO_IDENTIFIER}")

if [ "$1" = 'addzone' ]; then

     SERVER_IP=`curl -s -X GET "https://api.cloudflare.com/client/v4/zones/$CF_WHITELOTTO_WORK_ZONE_ID/dns_records/?name=review-apps.whitelotto.work&type=A" -H "Content-Type:application/json" -H "X-Auth-Email: $CF_EMAIL" -H "X-Auth-Key: $CF_AUTH_KEY" | jq -r '.result[] .content'`
     
     for WHITELOTTO_DOMAIN in ${WHITELOTTO_DOMAINS[@]};
     do
          curl -X POST "https://api.cloudflare.com/client/v4/zones/$CF_WHITELOTTO_WORK_ZONE_ID/dns_records" \
               -H "X-Auth-Email: $CF_EMAIL" \
               -H "X-Auth-Key: $CF_AUTH_KEY" \
               -H "Content-Type: application/json" \
               --data '{"type":"A","name":"'${WHITELOTTO_DOMAIN}'","content":"'$SERVER_IP'","proxied":false}'
     done

     for LOTTOPARK_DOMAIN in ${LOTTOPARK_DOMAINS[@]};
     do
          curl -X POST "https://api.cloudflare.com/client/v4/zones/$CF_LOTTOPARK_WORK_ZONE_ID/dns_records" \
          -H "X-Auth-Email: $CF_EMAIL" \
          -H "X-Auth-Key: $CF_AUTH_KEY" \
          -H "Content-Type: application/json" \
          --data '{"type":"A","name":"'${LOTTOPARK_DOMAIN}'","content":"'$SERVER_IP'","proxied":false}'
     done


elif [ "$1" = 'deletezone' ]; then

     for WHITELOTTO_DOMAIN in ${WHITELOTTO_DOMAINS[@]};
     do
          RECORD_ID=$(curl -X GET "https://api.cloudflare.com/client/v4/zones/$CF_WHITELOTTO_WORK_ZONE_ID/dns_records?type=A&name=${WHITELOTTO_DOMAIN}.whitelotto.work" \
          -H "X-Auth-Email: $CF_EMAIL" \
          -H "X-Auth-Key: $CF_AUTH_KEY" \
          -H "Content-Type: application/json" | jq -r '.result[0].id')

          curl -X DELETE "https://api.cloudflare.com/client/v4/zones/$CF_WHITELOTTO_WORK_ZONE_ID/dns_records/$RECORD_ID" \
          -H "X-Auth-Email: $CF_EMAIL" \
          -H "X-Auth-Key: $CF_AUTH_KEY" \
          -H "Content-Type: application/json"
     done

     for LOTTOPARK_DOMAIN in ${LOTTOPARK_DOMAINS[@]};
     do
          RECORD_ID=$(curl -X GET "https://api.cloudflare.com/client/v4/zones/$CF_LOTTOPARK_WORK_ZONE_ID/dns_records?type=A&name=${LOTTOPARK_DOMAIN}.lottopark.work" \
          -H "X-Auth-Email: $CF_EMAIL" \
          -H "X-Auth-Key: $CF_AUTH_KEY" \
          -H "Content-Type: application/json" | jq -r '.result[0].id')

          curl -X DELETE "https://api.cloudflare.com/client/v4/zones/$CF_LOTTOPARK_WORK_ZONE_ID/dns_records/$RECORD_ID" \
          -H "X-Auth-Email: $CF_EMAIL" \
          -H "X-Auth-Key: $CF_AUTH_KEY" \
          -H "Content-Type: application/json"
     done

fi

