<?php

namespace Tests\DataProviders;

class IpDataProvider
{
    public function ipAddressCases(): array
    {
        return [
            ['127.0.0.1'], //v4
            ['192.16.8.1'], //v4
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'], //v6
            ['2a02:2f04:c14:8600:c509:893c:336a:a453'] //v6
        ];
    }
}
