#!/bin/bash

#example key
#KEY: httpslottopark.work/wp-content/plugins/lotto-platform/public/images/lotteries/lottery_17.png

#example commands
#
#all whitelabels
#purge-page-cache.sh 'all'
#
#specific whitelabel by domain
#purge-page-cache.sh 'wl' 'lottopark.work'
#
#specific path
#purge-page-cache.sh 'regex' 'lottopark.work' '/'
#in this case /pl wont be removed
#
#everything starting from specific path
#purge-page-cache.sh 'regex' 'lottopark.work' '/pl/*'
#in this case /de wont be removed but /pl/test will be


slug="${2//./\\.}"

if [ "$#" -lt  "1" ]; then
        echo "Provide more arguments."
elif [[ $1 = "regex" ]]; then
        grep -lrnw "KEY: https$slug$3" /var/cache/nginx/$2/ | xargs --no-run-if-empty rm -rf
        grep -lrnw "KEY: authhttps$slug$3" /var/cache/nginx/$2/ | xargs --no-run-if-empty rm -rf
elif [[ $1 = "wl" ]]; then
        rm -rf /var/cache/nginx/$2/*
elif [[ $1 = "all" ]]; then
        rm -rf /var/cache/nginx/*/*
else
        str=`echo -n "https$2/$3" | md5sum | cut -d' ' -f1`
        path=`echo /var/cache/nginx/$2/${str: -1}/${str: -3:2}/$str`
        rm -rf $path
fi;
