<?php

namespace Helpers;

class Docker
{
    public static function getCurrentIp(): string
    {
        return exec("echo `/sbin/ip route|awk '/default/ { print $3 }'`");
    }
}