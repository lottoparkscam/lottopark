#!/bin/bash

SPATH='/usr/local/devops-tools/cicd/review-apps/whitelotto'
WHITELOTTO_IDENTIFIER=`${SPATH}/identifier.sh register $2`

cd /var/www/review-apps-router/

output=$(sudo -Hu vaultrecv /opt/vaultrecv/scripts/slack.sh)
access_token=$(echo $output | jq -r '.access_token')
channel=$(echo $output | jq -r '.channel')
username=$(echo $output | jq -r '.username')
icon_emoji=$(echo $output | jq -r '.icon_emoji')

if [ "$1" = 'start' ]; then
    REVERSE_MYSQL_PORT=`python3 router.py register $2 mysql`
    text="New review app has been deployed based on branch *$2*.
• White Lotto: <https://${WHITELOTTO_IDENTIFIER}.whitelotto.work>
• LottoPark: <https://${WHITELOTTO_IDENTIFIER}.lottopark.work>
• Casino: <https://casino.${WHITELOTTO_IDENTIFIER}.lottopark.work>
• Manager: <https://manager.${WHITELOTTO_IDENTIFIER}.lottopark.work>
• CRM: <https://admin.${WHITELOTTO_IDENTIFIER}.whitelotto.work>
• Empire: <https://empire.${WHITELOTTO_IDENTIFIER}.whitelotto.work>
• Affiliates: <https://aff.${WHITELOTTO_IDENTIFIER}.lottopark.work>
• MailHog: <https://mailhog.${WHITELOTTO_IDENTIFIER}.whitelotto.work>
• phpMyAdmin: <https://review-apps.whitelotto.work/phpmyadmin>
• MySQL Port: ${WHITELOTTO_IDENTIFIER}.whitelotto.work:${REVERSE_MYSQL_PORT}
"
elif [ "$1" = 'stop' ]; then
    text="The review app based on branch *$2* has been removed."
fi

curl -X POST -H "Authorization: Bearer $access_token" -H "Content-type: application/json; charset=UTF-8" --data "{\"channel\": \"$channel\", \"username\": \"$username\", \"text\": \"$text\", \"icon_emoji\": \"$icon_emoji\"}" https://slack.com/api/chat.postMessage

