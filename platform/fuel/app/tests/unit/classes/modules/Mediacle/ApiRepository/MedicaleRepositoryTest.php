<?php

namespace Unit\Modules\Mediacle\ApiRepository;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelAff;
use Models\WhitelabelCampaign;
use Models\WhitelabelPlugin;
use Models\WhitelabelPromoCode;
use Models\WhitelabelUserAff;
use Models\WhitelabelUserPromoCode;
use Modules\Mediacle\ApiRepository\MediacleClientFactory;
use Modules\Mediacle\ApiRepository\MediacleRepository;
use Modules\Mediacle\MediaclePlugin;
use Modules\Mediacle\Models\PlayerDataWhitelabelUserModelAdapter;
use Services\Logs\FileLoggerService;
use Psr\Http\Message\ResponseInterface;
use Test_Unit;

class MedicaleRepositoryTest extends Test_Unit
{
    private MediacleClientFactory $factory;
    private ResponseInterface $response;
    private MediacleRepository $repo;
    private FileLoggerService $fileLoggerService;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->createMock(MediacleClientFactory::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);
        $this->repo = new MediacleRepository($this->factory, $this->fileLoggerService);
        $this->response = $this->createMock(ResponseInterface::class);
    }

    /** @test */
    public function save__player_data(): void
    {
        // Given
        $key = 'key';
        $firstName = 'first';
        $lastName = 'last';
        $playerEmail = 'email';
        $phoneNumber = '+49123123123';
        $company = 'My Company';
        $player_id = '1234567';
        $brand = 'DoubleJack';
        $country_code = 'US';
        $account_opening_date = '2021-04-01 12:00:00';
        $promocode = 'promo12';
        $tracking_id = '342134';
        $btag = 'btag123test1234as';
        $timestamp = Carbon::parse($account_opening_date)->timestamp;

        $user = new WhitelabelUser();
        $user->name = $firstName;
        $user->surname = $lastName;
        $user->email = $playerEmail;
        $user->phone = $phoneNumber;
        $user->company = $company;
        $user->token = $player_id;
        $user->whitelabel = new Whitelabel(['name' => $brand, 'prefix' => 'DJ']);
        $user->last_country = $country_code;
        $user->date_register = $account_opening_date;
        $user->whitelabel_user_promo_code = new WhitelabelUserPromoCode([]);
        $user->whitelabel_user_promo_code->whitelabel_promo_code = new WhitelabelPromoCode([]);
        $user->whitelabel_user_promo_code->whitelabel_promo_code->whitelabel_campaign = new WhitelabelCampaign(['token' => $promocode, 'type' => 2]);
        $user->whitelabel_user_aff = new WhitelabelUserAff(['btag' => $btag]);
        $user->whitelabel_user_aff->whitelabel_aff = new WhitelabelAff(['token' => $tracking_id]);

        $mediaclePlugin = new WhitelabelPlugin();
        $mediaclePlugin->plugin = MediaclePlugin::NAME;
        $mediaclePlugin->options = ['key' => $key];
        $plugins = [$mediaclePlugin];
        $user->whitelabel->whitelabel_plugins = $plugins;

        $adapter = new PlayerDataWhitelabelUserModelAdapter($user);

        $client = $this->createMock(Client::class);

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($client);

        $account_opening_date = urlencode($account_opening_date);
        $company = urlencode($company);
        $expectedUri = "apisignup.ashx?key={$key}&fnm={$firstName}&lnm={$lastName}&eml={$playerEmail}&phone_number=%2B49123123123&company={$company}&player_id=DJU{$player_id}&brand={$brand}&country_code={$country_code}&account_opening_date="
            . "{$account_opening_date}&promocode={$promocode}&tracking_id={$tracking_id}&btag={$btag}&timestamp={$timestamp}";

        $client->expects($this->once())
            ->method('request')
            ->with('POST', $expectedUri)
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        // When
        $this->repo->save($adapter);
    }
}
