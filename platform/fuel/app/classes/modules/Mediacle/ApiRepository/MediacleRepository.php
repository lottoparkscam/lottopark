<?php

namespace Modules\Mediacle\ApiRepository;

use Modules\Mediacle\Models\MediaclePlayerRegistrationData;
use Services\Logs\FileLoggerService;

class MediacleRepository implements SavePlayerContract
{
    public const URI = 'apisignup.ashx?key={key}&fnm={first_name}&lnm={last_name}&eml={player_email}' .
    '&phone_number={phone_number}&company={company}&player_id={player_id}&brand={brand}&country_code={country_code}' .
    '&account_opening_date={account_opening_date}&promocode={promocode}&tracking_id={tracking_id}' .
    '&btag={btag}&timestamp={timestamp}';

    private MediacleClientFactory $factory;
    private FileLoggerService $fileLoggerService;

    public function __construct(MediacleClientFactory $factory, FileLoggerService $fileLoggerService)
    {
        $this->factory = $factory;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function save(MediaclePlayerRegistrationData $player): void
    {
        $client = $this->factory->create();

        $uri = str_replace([
            '{key}',
            '{first_name}',
            '{last_name}',
            '{player_email}',
            '{phone_number}',
            '{company}',
            '{player_id}',
            '{brand}',
            '{country_code}',
            '{account_opening_date}',
            '{promocode}',
            '{tracking_id}',
            '{btag}',
            '{timestamp}',
        ], [
            $player->getTrackingIdentityKey(),
            urlencode($player->getFirstName()),
            urlencode($player->getLastName()),
            urlencode($player->getEmail()),
            urlencode($player->getPhoneNumber()),
            urlencode($player->getCompany()),
            urlencode($player->getPlayerId()),
            urlencode($player->getBrand()),
            urlencode($player->getCountryCode()),
            urlencode($player->getAccountOpeningDate()),
            urlencode($player->getPromoCode()),
            urlencode($player->getTrackingId()),
            urlencode($player->getBtag()),
            urlencode($player->getTimeStamp()),
        ], self::URI);

        $result = $client->request('POST', $uri);

        if ($result->getStatusCode() > 201) {
            $this->fileLoggerService->error(
                'mediacle failed to receive register event, status code > 201, ' . $result->getStatusCode()
            );
        }
    }
}
