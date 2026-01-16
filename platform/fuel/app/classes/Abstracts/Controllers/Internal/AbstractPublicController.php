<?php

namespace Abstracts\Controllers\Internal;

use Container;
use Core\App;
use Fuel\Core\Controller_Rest;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\Wordpress\SecurityHelper;
use Helpers\SanitizerHelper;
use LanguageHelper;
use Lotto_Helper;
use Lotto_Settings;
use Modules\View\ViewHelper;
use Repositories\WhitelabelRepository;
use Services\MaintenanceService;

/**
 * We block spain IPs here
 * WIth one exception: api/slots path
 */
abstract class AbstractPublicController extends Controller_Rest
{
    public const API_LANGUAGE_SHORTCODE = 'API_LANGUAGE_SHORTCODE';

    protected bool $isCasino = false;
    protected string $languageWithLocale = 'en_GB.utf8';
    /** @var callable */
    private $setOwnHeadersCallback;

    public function before()
    {
        parent::before();

        /**
         * This little trick allows us to test controllers without any HTTP request
         * @var App $app
         */
        $app = Container::get(App::class);
        if ($app->isTest()) {
            return true;
        }

        $maintenanceService = new MaintenanceService();
        $maintenanceService->setMaintenanceDomainFilename(APPPATH . '.maintenance-domain');
        $domainFromUrl = Lotto_Helper::getWhitelabelDomainFromUrl();
        if (!empty(Input::get('is_casino'))) {
            $this->isCasino = true;
            if ($maintenanceService->isWhitelabelDomainUnderMaintenance($domainFromUrl)) {
                die();
            }
        }

        $language = Input::get('language');
        if (!empty($language) && is_string($language)) {
            $this->languageWithLocale = SanitizerHelper::sanitizeString($language);

            define(self::API_LANGUAGE_SHORTCODE, LanguageHelper::getLanguageCodeFromLocale($this->languageWithLocale));

            putenv('LC_ALL=' . $this->languageWithLocale);
            setlocale(LC_ALL, $this->languageWithLocale);

            // Setup translations from wordpress's path
            bindtextdomain('lotto-platform', APPPATH . '../../../wordpress/wp-content/plugins/lotto-platform/languages/gettext');
            textdomain('lotto-platform');
        }

        Lotto_Settings::getInstance()->set('locale_default', $this->languageWithLocale);

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Origin, Content-Type');

        if (isset($this->setOwnHeadersCallback)) {
            ($this->setOwnHeadersCallback)();
        }

        /** @var WhitelabelRepository $whitelabelRepository */
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->findOneByDomain($domainFromUrl);
        $shouldRedirect = SecurityHelper::shouldBlockSpainForV1($whitelabel->to_array());
        if ($shouldRedirect) {
            Response::redirect("https://blocked.{$whitelabel['domain']}");
        }
    }

    protected function returnResponse(array $data, int $code = 200): Response
    {
        $contentType = ['Content-type' => 'application/json'];
        return new Response(json_encode($data), $code, $contentType);
    }

    protected function returnTwigView(string $viewName, array $dataset = []): Response
    {
        return new Response(ViewHelper::render($viewName, $dataset));
    }

    /** This function allows us to omit headers while testing */
    public function setOwnHeaders(callable $setOwnHeaders): void
    {
        $this->setOwnHeadersCallback = $setOwnHeaders;
    }
}
