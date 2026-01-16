<?php

namespace Tests\Unit\Classes\Services;

use Exception;
use GGLib\Lcs\Client\Http\HttpLcsClient;
use GGLib\Lcs\Client\Request\GetLotteryTicketImageRequest;
use GGLib\Lcs\Client\Response\GetLotteryTicketImageResponse;
use GGLib\Lcs\Dto\LotteryTicketImage;
use Services\LcsService;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LcsServiceTest extends Test_Unit
{
    private HttpLcsClient $httpLcsClient;
    private GetLotteryTicketImageRequest $getLotteryTicketImageRequest;
    private GetLotteryTicketImageResponse $getLotteryTicketImageResponse;
    private LotteryTicketImage $lotteryTicketImage;
    private ConfigContract $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->httpLcsClient = $this->createMock(HttpLcsClient::class);
        $this->getLotteryTicketImageRequest = $this->createMock(GetLotteryTicketImageRequest::class);
        $this->getLotteryTicketImageResponse = $this->createMock(GetLotteryTicketImageResponse::class);
        $this->lotteryTicketImage = $this->createMock(LotteryTicketImage::class);
        $this->config = $this->container->get(ConfigContract::class);
    }

    /** @test */
    public function getLotteryScansFromLcs_lcsExists()
    {
        $_ENV['LCS_URL_BASE'] = 'asd';
        $_ENV['LCS_SALE_POINT_KEY'] = 'asd';
        $_ENV['LCS_SALE_POINT_SECRET'] = 'asd';

        $this->httpLcsClient
            ->expects($this->once())
            ->method('getLotteryTicketImage')
            ->with($this->getLotteryTicketImageRequest)
            ->willReturn($this->getLotteryTicketImageResponse);

        $this->getLotteryTicketImageResponse
            ->expects($this->once())
            ->method('getImage')
            ->willReturn($this->lotteryTicketImage);

        $this->lotteryTicketImage
            ->expects($this->once())
            ->method('getData');

        $lcsService = new LcsService($this->httpLcsClient, $this->config);
        $lcsService->getLotteryScansFromLcs($this->getLotteryTicketImageRequest);
    }

    /** @test */
    public function getLotteryScansFromLcs_lcsNotExists()
    {
        $_ENV['LCS_URL_BASE'] = '';
        $_ENV['LCS_SALE_POINT_KEY'] = '';
        $_ENV['LCS_SALE_POINT_SECRET'] = '';

        $this->expectException(Exception::class);
        new LcsService($this->httpLcsClient, $this->config);
    }

    /** @test  */
    public function getLotteryScansFromLcs_lcsUrlBaseNotExists()
    {
        $_ENV['LCS_URL_BASE'] = '';
        $_ENV['LCS_SALE_POINT_KEY'] = 'asd';
        $_ENV['LCS_SALE_POINT_SECRET'] = 'asd';

        $this->expectException(Exception::class);
        new LcsService($this->httpLcsClient, $this->config);
    }

    /** @test */
    public function getLotteryScansFromLcs_lcsSalePointKeyNotExists()
    {
        $_ENV['LCS_URL_BASE'] = 'asd';
        $_ENV['LCS_SALE_POINT_KEY'] = 'asd';
        $_ENV['LCS_SALE_POINT_SECRET'] = '';

        $this->expectException(Exception::class);
        new LcsService($this->httpLcsClient, $this->config);
    }

    /** @test */
    public function getLotteryScansFromLcs_lcsSalePointSecretNotExists()
    {
        $_ENV['LCS_URL_BASE'] = 'asd';
        $_ENV['LCS_SALE_POINT_KEY'] = '';
        $_ENV['LCS_SALE_POINT_SECRET'] = 'asd';

        $this->expectException(Exception::class);
        new LcsService($this->httpLcsClient, $this->config);
    }
}
