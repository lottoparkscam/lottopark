<?php

namespace Fuel\Tasks\Seeders;

final class SlotsSlotegratorProviderData extends AbstractSlotProviderData
{
    protected const SLOT_PROVIDER_SLUG = "slotegrator";
    protected const SLOT_PROVIDER_API_URL = "https://staging.slotegrator.com/api/index.php/v1";
    protected const SLOT_PROVIDER_INIT_GAME_PATH = "/games/init";
    protected const SLOT_PROVIDER_INIT_DEMO_GAME_PATH = "/games/init-demo";

    // we seed lottopark and faireum because it has different stagings; mainly it returns different games.
    // it may be useful during testing/developing
    protected const SLOT_PROVIDER_API_CREDENTIALS = [
        'lottopark_merchant_id' => 'd15595796357c074c1685d720a8d9d9f',
        'lottopark_merchant_key' => 'fe6acdaca5daa91ce66f7cd4345e334332c9800e',
        'faireum_merchant_id' => '749aa539f9e0728a9006def7858fd926',
        'faireum_merchant_key' => 'be4c7e30992f9bcf738282773e781d39b1d65ed9'
    ];
    protected const SLOT_PROVIDER_GAME_LIST_PATH = "/games";
    protected const WHITELIST_IP = ["54.36.214.165", "51.91.199.2","51.254.8.120"];
    protected const PROD_WHITELIST_IP = [
        "51.178.89.36",
        "54.38.232.199",
        "51.178.172.87",
        "51.77.139.4",
        "164.132.170.53",
        "217.182.219.207",
        "81.171.0.90",
        "89.149.192.37",
        "89.38.97.212",
        "37.48.115.168",
        "195.201.3.159",
        "146.59.144.187",
        "213.227.135.179",
        "213.227.135.180"
    ];
}
