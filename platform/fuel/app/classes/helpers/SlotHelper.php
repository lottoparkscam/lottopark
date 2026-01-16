<?php

namespace Helpers;

use Container;
use Models\SlotGame;
use Fuel\Core\Input;

final class SlotHelper
{

    /**
     * GUIDELINES - READ BEFORE EDITING
     * https://bookstack.gginternational.work/books/wl-team-white-lotto/page/restricted-countries
     */
    const GAME_ART_RESTRICTED_COUNTRIES = ['FR', 'BE', 'SE', 'DK', 'BG', 'CH', 'EE', 'CY', 'IL', 'GB', 'HK', 'AU', 'NZ', 'US', 'NL', 'LT', 'IT', 'HR', 'ES', 'PT', 'RO', 'CO', 'AS', 'BN', 'KH', 'CN', 'IN', 'ID', 'JP', 'KR', 'KP', 'LA', 'MO', 'MY', 'MN', 'BU', 'PG', 'PH', 'SG', 'LK', 'TW', 'TH', 'TL', 'VN'];
    const PRAGMATIC_PLAY_RESTRICTED_COUNTRIES = ['AU', 'BS', 'BE', 'BG', 'CA', 'CO', 'DK', 'FR', 'GR', 'ES', 'NL', 'IN', 'IR', 'IL', 'KP', 'LT', 'MM', 'PH', 'PT', 'ZA', 'RO', 'RS', 'SG', 'SY', 'SE', 'TW', 'UA', 'US', 'IT', 'GB', 'AE', 'GI', 'BL', 'GF', 'GP', 'MF', 'MQ', 'NC', 'PF', 'PM', 'RE', 'TF', 'WF', 'YT', 'AS', 'GU', 'MP', 'PR', 'UM', 'VI'];
    const EVOLUTION_RESTRICTED_COUNTRIES = ['IQ', 'AU', 'CU', 'IR', 'KP', 'SS', 'SD', 'SY', 'TW', 'UA', 'VE', 'AF', 'AL', 'BB', 'BF', 'KY', 'HT', 'JM', 'JO', 'ML', 'MT', 'MM', 'NI', 'PA', 'PH', 'SN', 'TR', 'UG', 'YE', 'ZW', 'BE', 'CA', 'CO', 'HR', 'CZ', 'DK', 'EE', 'FR', 'GE', 'DE', 'GR', 'IT', 'LV', 'LT', 'MX', 'NL', 'PT', 'RO', 'ZA', 'ES', 'SE', 'CH', 'US', 'CM', 'CD', 'GI', 'MZ', 'NG', 'TT', 'AE', 'VU', 'VN', 'AT', 'AR', 'AM', 'GG', 'BS', 'BA', 'BW', 'BR', 'BG', 'CL', 'CR', 'CW', 'CY', 'DO', 'EC', 'SZ', 'FI', 'GH', 'HU', 'IE', 'IM', 'LS', 'NA', 'NO', 'PY', 'PE', 'PL', 'RW', 'RS', 'SK', 'SR', 'GB', 'TZ'];
    const RESTRICTED_COUNTRIES_PER_PROVIDER = [
        'Amatic' => ['AF', 'AL', 'DZ', 'AO', 'AG', 'AM', 'AU', 'AT', 'KH', 'CN', 'CU', 'CY', 'EC', 'EE', 'FR', 'GE', 'GY', 'HK', 'ID', 'IR', 'IQ', 'IL', 'KW', 'LA', 'LY', 'LI', 'MO', 'MM', 'NA', 'AN', 'NI', 'KP', 'PK', 'PA', 'PG', 'PH', 'SG', 'SK', 'ZA', 'KR', 'SD', 'CH', 'SY', 'TW', 'TN', 'UG', 'US', 'VN', 'YE', 'ZW', 'GB'],
        'BoomingGames' => ['AU', 'BE', 'BG', 'KY', 'CZ', 'CO', 'DK', 'EE', 'IR', 'IQ', 'IL', 'IT', 'LV', 'LT', 'KP', 'PT', 'RO', 'SA', 'SG', 'SK', 'ES', 'SE', 'UK', 'US', 'VI', 'UM', 'GU', 'PR', 'AS', 'VA', 'MP', 'ZA', 'SE',],
        'Booongo' => ['US', 'IL', 'NL', 'FR', 'SG', 'UA'],
        'CT Interactive' => ['US', 'CA', 'RU', 'KZ', 'VN', 'TH', 'CN', 'IL', 'JP', 'CY'],
        'CT Gaming' => ['US', 'CA', 'RU', 'KZ', 'VN', 'TH', 'CN', 'IL', 'JP', 'CY'],
        'Dlv' => ['AU', 'BE', 'KY', 'CW', 'FR', 'HK', 'IR', 'IQ', 'IE', 'IL', 'IT', 'JP', 'MM', 'NL', 'KP', 'PL', 'SA', 'SG', 'SY', 'TR', 'US', 'GB', 'VA', 'LV'],
        'Endorphina' => ['FR', 'AU', 'GR'],
        'EurasianGaming' => ['AT', 'AU', 'AW', 'BR', 'BQ', 'CN', 'CW', 'ES', 'FR', 'GB', 'NL', 'SG', 'SX', 'TW', 'US', 'VN', 'AS', 'GU', 'MP', 'PR', 'UM', 'VI'],
        'Evoplay' => ['AF', 'AU', 'CU', 'ER', 'ET', 'FR', 'IR', 'IQ', 'IL', 'JO', 'LY', 'KP', 'PK', 'PH', 'RW', 'SG', 'SO', 'PS', 'SD', 'SY', 'TN', 'US', 'GB', 'YE', 'AW', 'BQ', 'SX', 'NL', 'SS', 'ZW', 'VE', 'UG', 'PA', 'ML', 'LT', 'LR', 'JM', 'GH', 'DE', 'DZ', 'BB', 'BU', 'BW', 'CD', 'GU', 'PR', 'VI', 'MP', 'AS', 'GY', 'GP', 'MQ', 'YT', 'RE', 'GY', 'GP', 'MQ', 'YT', 'RE'],
        'GameArt' => self::GAME_ART_RESTRICTED_COUNTRIES,
        'GameArt Branded' => self::GAME_ART_RESTRICTED_COUNTRIES,
        'GameArt Branded Premium' => self::GAME_ART_RESTRICTED_COUNTRIES,
        'Gamshy' => ['US', 'CN', 'MO', 'HK', 'IT', 'GB'],
        'Green Jade' => ['AF', 'AG', 'BE', 'BG', 'CU', 'CY', 'FR', 'HK', 'IR', 'IQ', 'IL', 'QC', 'LY', 'MO', 'MY', 'AN', 'SG', 'SD', 'SY', 'PH', 'US', 'TN'],
        'Habanero' => ['AU', 'BY', 'CO', 'HR', 'BG', 'DK', 'EE', 'GE', 'GI', 'IM', 'LV', 'LT', 'MT', 'PA', 'PT', 'RO', 'ES', 'SE', 'NL', 'US', 'UK', 'SG', 'CY', 'PH', 'FR', 'ZA', 'TW', 'IT', 'CU', 'CW', 'CF', 'CD', 'ER', 'GW', 'IR', 'IQ', 'LB', 'LY', 'ML', 'KP', 'SO', 'SS', 'SD', 'SY', 'YE', 'AR', 'CG', 'CA', 'GR', 'DE'],
        'KAGaming' => ['TW'],
        'NetGame' => ['US', 'NL', 'CW', 'GB'],
        'OneTouch' => ['AU', 'KY', 'FR', 'HK', 'HU', 'IR', 'IQ', 'IL', 'MM', 'KP', 'PL', 'SA', 'SG', 'CH', 'SY', 'TR', 'GB', 'US', 'VA', 'UM', 'VI', 'PR', 'AS', 'GU', 'MP'],
        'Platipus' => ['IL', 'US'],
        'Playson' => ['AU', 'IL', 'US', 'GB', 'FR', 'LT', 'NL', 'GR', 'GE',],
        'PragmaticPlay' => self::PRAGMATIC_PLAY_RESTRICTED_COUNTRIES,
        'PragmaticPlayLive' => self::PRAGMATIC_PLAY_RESTRICTED_COUNTRIES,
        'Blueprint' => ['AU', 'AT', 'BG', 'BE', 'CO', 'CZ', 'CU', 'HR', 'CA', 'DK', 'EE', 'FR', 'DE', 'GI', 'HU', 'HK', 'IQ', 'IT', 'IR', 'IL', 'KP', 'KW', 'LT', 'LV', 'MM', 'NL', 'PH', 'SS', 'SD', 'SY', 'SG', 'CH', 'ES', 'SE', 'PT', 'RU', 'RO', 'UK', 'US', 'AF', 'AS', 'AO', 'BA', 'KH', 'AW', 'BQ', 'CW', 'SX', 'ET', 'LA', 'UG', 'VU', 'YE', 'MT'],
        'Ezugi' => ['CA', 'IL', 'IR', 'TW', 'UA', 'VE', 'SD', 'SS', 'CU', 'AF', 'AS', 'AO', 'AU', 'BE', 'BA', 'HR', 'HR', 'CZ', 'KP', 'DK', 'AW', 'BQ', 'CW', 'SX', 'EE', 'ET', 'FR', 'HU', 'IQ', 'IT', 'LA', 'LV', 'LT', 'PH', 'RO', 'SG', 'ES', 'SY', 'SE', 'UG', 'GB', 'US', 'VU', 'YE', 'MT', 'BG', 'CO', 'ZA', 'NL',],
        'Quickspin' => ['AF', 'AM', 'AR', 'AS', 'AU', 'AW', 'BA', 'BE', 'BQ', 'BR', 'CH', 'CO', 'CZ', 'DE', 'DK', 'EE', 'ET', 'FI', 'FR', 'GB', 'GE', 'GR', 'HK', 'HR', 'HU', 'IQ', 'IT', 'KH', 'KP', 'LA', 'LT', 'LV', 'MO', 'MT', 'MX', 'NO', 'PE', 'PH', 'PL', 'PT', 'RO', 'RU', 'SE', 'SI', 'SK', 'SX', 'SY', 'TR', 'UA', 'UG', 'US', 'UM', 'VI', 'VU', 'YE'],
        'Thunderkick' => ['AU', 'BE', 'CZ', 'KP', 'DK', 'IR', 'IT', 'LT', 'RO', 'SY', 'GB', 'US', 'FR', 'HK', 'GF', 'PF', 'GP' , 'MQ', 'YT', 'NC', 'RE', 'MF', 'BL', 'PM', 'WF', 'GU', 'PR', 'VI', 'MP', 'AS', 'MM', 'SD', 'ZW', 'VE', 'ES', 'PT', 'SE', 'NG', 'EE', 'UA', 'CA', 'LY', 'NL'],
        'Yggdrasil' => ['AF', 'GG', 'AS', 'AO', 'AU', 'BE', 'BA', 'KH', 'HR', 'CZ', 'KP', 'DK', 'CW', 'AW', 'ET', 'HU', 'IQ', 'IR', 'IT', 'LA', 'LV', 'LT', 'RO', 'SG', 'SY', 'TR' ,'UG', 'GB', 'US', 'VU', 'YE', 'DE', 'ES', 'PT', 'EE', 'FR', 'NL', 'AL', 'AD', 'CA', 'GR', 'IS', 'IE', 'LI', 'LU', 'MC', 'ME', 'NO', 'SM', 'RS', 'SK', 'SI', 'TW', 'AT'],
        'RedRake' => ['AU', 'BE', 'BY', 'CA', 'CZ', 'DK', 'EE', 'ES', 'FR', 'GB', 'HR', 'IL', 'IT', 'LT', 'LV', 'NL', 'PL', 'PT', 'RO', 'US', 'ZA', 'AS', 'GU', 'MP', 'PR', 'UM', 'VI'],
        'Push Gaming' => ['AF', 'AL', 'AS', 'AO', 'AU', 'BA', 'BS', 'BB', 'BW', 'JM', 'NI', 'SA', 'SN', 'SY', 'AE', 'UG', 'VU', 'BF', 'CU', 'KH', 'KY', 'ET', 'DK', 'EG', 'ER', 'EE', 'FR', 'GF', 'GL', 'GP', 'GU', 'VA', 'HK', 'IR', 'IQ', 'IL', 'IT', 'KW', 'MY', 'MH', 'MM', 'MO', 'NG', 'KP', 'PL', 'PR', 'QA', 'TF', 'PF', 'SG', 'ZA', 'ES', 'SD', 'SE', 'TW', 'GB', 'US', 'UM', 'VI', 'YE', 'ZW'],
        'RevolverGaming' => ['AF', 'AL', 'DZ', 'AO', 'AU', 'BE', 'BG', 'CZ', 'CO', 'DK', 'EC', 'FR', 'GY', 'HK', 'IR', 'IQ', 'IL', 'IT', 'LT', 'KP', 'PH', 'PL', 'PT', 'PR', 'RO', 'SA', 'SG', 'ES', 'US', 'VA', 'GB', 'GF', 'PF', 'GP' , 'MQ', 'YT', 'NC', 'RE', 'MF', 'BL', 'PM', 'WF'],
        'Spadegaming' => ['TW', 'PH'],
        'Spinomenal' => ['GB', 'US', 'IL', 'AU', 'AF', 'ET', 'GY', 'IR', 'IQ', 'PK', 'PS', 'LK', 'SY', 'TT', 'UG', 'VU', 'YE',],
        'RetroGaming' => ['GB', 'US', 'IL', 'AU', 'AF', 'ET', 'GY', 'IR', 'IQ', 'PK', 'PS', 'LK', 'SY', 'TT', 'UG', 'VU', 'YE',],
        'SuperSpadeGames' => ['US', 'RU', 'BY', 'UA', 'GG',],
        'Tomhorn' => ['IL', 'US', 'GB'],
        'TripleCherry' => ['AF', 'DZ', 'AO', 'AG', 'KH', 'CN', 'QC', 'CU', 'CY', 'GY', 'HK', 'ID', 'IQ', 'KW', 'LY', 'MO', 'MM', 'NA', 'AN', 'KP', 'PK', 'PG', 'SD', 'SY', 'UG', 'US', 'UM', 'VI', 'PR', 'AS', 'GU', 'MP'],
        'Truelab' => ['US', 'MP', 'PR', 'VI', 'GU', 'AS', 'CW', 'FR', 'GF', 'PF', 'TF', 'GI', 'MT', 'NL', 'RU', 'RE', 'BQ', 'SX', 'PM', 'BL', 'WF', 'UA', 'AU'],
        'Vivogaming' => ['CR', 'IR', 'IQ', 'SY', 'US', 'UY', 'IL'],
        'Betsoft' => ['US', 'IL', 'SY', 'IR', 'AR', 'CO', 'CR'],
        'Wazdan' => ['AF', 'AM', 'AG', 'AW', 'BE', 'BG', 'BQ', 'CA', 'CH', 'CN', 'CU', 'CW', 'CY', 'DK', 'ES', 'FR', 'HK', 'IR', 'IQ', 'IT', 'JP', 'LT', 'LV', 'LY', 'MO', 'MY', 'NL', 'PH', 'PL', 'PT', 'RS', 'SD', 'SE', 'SG', 'SX', 'SY', 'TR', 'US', 'UK', 'BL', 'GF', 'GP', 'MF', 'MQ', 'NC', 'PF', 'PM', 'RE', 'TF', 'WF', 'YT',],
        'XPG' => ['BG', 'IN', 'IL', 'MK', 'US'],
        'GreenJadeGames' => ['AF', 'AG', 'BE', 'BG', 'CU', 'CY', 'FR', 'HK', 'IR', 'IQ' ,'IL', 'QC', 'LY', 'MO', 'MY', 'AN', 'SG', 'SD', 'SY', 'PH', 'UM', 'VI', 'PR', 'AS', 'GU', 'MP', 'TN'],
        'Evolution' => self::EVOLUTION_RESTRICTED_COUNTRIES,
        'Evolution2' => self::EVOLUTION_RESTRICTED_COUNTRIES,
        'RTG SLOTS' => ['AF', 'GG', 'AS', 'AO', 'AU', 'BE', 'BA', 'KH', 'HR', 'CZ', 'KP', 'DK', 'AW', 'CW', 'EE', 'ET', 'FR', 'HU', 'IQ', 'IT', 'LA', 'LV', 'LT', 'PH', 'RO', 'SG', 'ES', 'SY', 'SE', 'UG', 'GB', 'US', 'VU', 'YE', 'MT', 'PH', 'DE', 'ZA',],
        'SmartSoft' => ['GR', 'GE', 'PT', 'GI', 'PE'],
        'Caleta' => ['AU', 'IR', 'IQ', 'KP', 'SY', 'UK', 'US',],
        'NetEnt' => ['AU', 'CU', 'IR', 'KP', 'SS', 'SD', 'SY', 'TW', 'UA', 'VE', 'AF', 'AL', 'BB', 'BF', 'KY', 'HT', 'JM', 'JO', 'ML', 'MT', 'MM', 'NI', 'PA', 'PH', 'SN', 'TR', 'UG', 'YE', 'ZW', 'BE', 'HR', 'CZ', 'DK', 'EE', 'FR', 'IT', 'LV', 'LT', 'RO', 'ES', 'SE', 'US', 'VU', 'GG', 'BS', 'BA', 'BW', 'EC', 'GH', 'HU', 'GB', 'AS', 'AO', 'KH', 'AW', 'BQ', 'CW', 'SX', 'IQ', 'LA', 'SG', 'MA', 'DZ', 'GY', 'HK', 'IL', 'KW', 'PK', 'NA', 'ET',],
        'Spribe' => ['GE', 'AM', 'RU',],
        'ThreeOaks' => ['AE', 'AF', 'AU', 'BE', 'CH', 'DK', 'ES', 'FR', 'GB', 'GE', 'GW', 'HR', 'HT', 'IL', 'IQ', 'IR', 'JM', 'KP', 'LB', 'LY', 'MM', 'NI', 'NL', 'PA', 'PH', 'PK', 'PT', 'RO', 'SO', 'SS', 'SY', 'TR', 'US', 'VE', 'YE', 'ZW'],
    ];

    const ALLOWED_GAMES_PER_DOMAIN = [];

    public static function getRestrictedProviders(): ?array
    {
        $restrictedCountriesPerProvider = self::RESTRICTED_COUNTRIES_PER_PROVIDER;

        /** @var string $userCountryCode on loc env will be default UK 
         * because get_IP on loc env returns LAN IP
         */
        $userCountryCode = CountryHelper::iso() ?: 'UK';
        $disallowedProvidersForThisCountry = [];

        if (empty($userCountryCode)) {
            return [];
        }

        foreach ($restrictedCountriesPerProvider as $provider => $countries) {
            if (in_array($userCountryCode, $countries)) {
                $disallowedProvidersForThisCountry[] = $provider;
            }
        }

        return $disallowedProvidersForThisCountry;
    }

    /*
     * Converts the GamesOrder array to a simple array containing only game IDs sorted by keys.
     * This method is especially created for query ORDER BY FIELD statement
     */
    public static function getGamesIdsSortedByGameOrder(array $gamesOrder): array
    {
        $gamesOrderNew = [];
        foreach ($gamesOrder as $gameOrder) {
            $gamesOrderNew[(int) $gameOrder['gameOrder']] = (int) $gameOrder['gameId'];
        }

        ksort($gamesOrderNew);

        return $gamesOrderNew;
    }

    /** This function is used to prepare game data for frontend (with links) - its pointless
     * to send useless data for user.
     * @param SlotGame[] $slotGames;
     * @return array key by uuid
     */
    public static function prepareGameData(array $slotGames): array
    {
        $games = [];
        foreach ($slotGames as $game) {
            $hasDemo = $game->hasDemo;
            $uuid = $game->uuid;
            $shouldDisableDemoIfGameHasLobby = $game->hasLobby;
            if ($shouldDisableDemoIfGameHasLobby) {
                $hasDemo = false;
            }

            $gameData = [
                'name' => $game->name,
                'image' => $game->image,
                'has_lobby' => $game->hasLobby,
                'is_mobile' => $game->isMobile,
                'provider' => $game->provider,
                'type' => $game->type,
                'has_demo' => $hasDemo,
                'technology' => $game->technology
            ];

            $games[$uuid] = $gameData;
        }

        return $games;
    }

    public static function getModeSwitchData(): array
    {
        $demoParams = '&mode=demo';
        $currentMode = !empty(Input::get('mode')) ? 'demo' : 'real';

        $isDemo = $currentMode === 'demo';
        $oppositeMode = $isDemo ? 'real' : 'demo';
        $currentUrlWithParams = UrlHelper::getCurrentUrlWithParams();

        if ($isDemo) {
            $oppositeModeUrl = str_replace($demoParams, '', $currentUrlWithParams);
        } else {
            $oppositeModeUrl = $currentUrlWithParams . $demoParams;
        }

        return [
            'currentMode' => $currentMode,
            'oppositeMode' => $oppositeMode,
            'oppositeModeUrl' => $oppositeModeUrl
        ];
    }

    /** Return true if all games are allowed */
    public static function getAllowedGameUuids(string $domain): array|bool
    {
        $specificGames = self::ALLOWED_GAMES_PER_DOMAIN[$domain] ?? false;
        $hasNotSpecificGames = !$specificGames;
        if ($hasNotSpecificGames) {
            return true;
        }

        $allUuids = [];
        foreach (self::ALLOWED_GAMES_PER_DOMAIN[$domain] as $uuids) {
            $allUuids = array_merge($allUuids, $uuids);
        }

        return $allUuids;
    }

    /** If whitelabel has not specific filters, function returns true */
    public static function getAllowedGameTypes(string $domain): array|bool
    {
        return empty(self::ALLOWED_GAMES_PER_DOMAIN[$domain]) ?
            true :
            array_keys(self::ALLOWED_GAMES_PER_DOMAIN[$domain]);
    }

    /** If whitelabel has not specific filters, function returns true */
    public static function getAllowedUuidsPerType(string $type): array|bool
    {
        $whitelabel = Container::get('whitelabel');
        $type = strtoupper($type);
        return self::ALLOWED_GAMES_PER_DOMAIN[$whitelabel->domain][$type] ?? true;
    }
}
