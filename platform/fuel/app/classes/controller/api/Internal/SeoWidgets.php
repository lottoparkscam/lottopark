<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Response;
use Presenters\Api\Internal\SeoWidgetsPresenter;
use Services\Api\Internal\SeoWidgetsService;
use Validators\SeoWidgetsValidator;

class Controller_Api_Internal_SeoWidgets extends AbstractPublicController
{
    public SeoWidgetsValidator $validator;
    private SeoWidgetsPresenter $seoWidgetsPresenter;
    private SeoWidgetsService $seoWidgetsService;

    public function before()
    {
        $this->setOwnHeaders(function () {
            header('Content-Security-Policy: frame-ancestors *');
        });

        parent::before();

        $this->validator = Container::get(SeoWidgetsValidator::class);
        $this->seoWidgetsPresenter = Container::get(SeoWidgetsPresenter::class);
        $this->seoWidgetsService = Container::get(SeoWidgetsService::class);
    }

    /** Use language property in url query to change language e.g. en_GB.utf8 */
    public function get_index(): Response
    {
        $isRequestInvalid = $this->validator->isNotValid();
        if ($isRequestInvalid) {
            $errors = json_encode($this->validator->getErrors());
            return $this->returnResponse([
                'message' => "Request is invalid. $errors",
            ]);
        }

        [$lotterySlug, $widgetType, $orderUrl, $currencyCode] = $this->validator->getValidatedProperties([
            'lotterySlug',
            'widgetType',
            'orderUrl',
            'currencyCode',
        ]);

        if ($this->seoWidgetsService->isWidgetNotAvailable($lotterySlug)) {
            return $this->returnResponse([
                'message' => 'This lottery is not available yet.'
            ]);
        }

        // Set correct whitelabel context
        Lotto_Settings::getInstance()->set('whitelabel', Container::get('whitelabel'));

        try {
            $seoWidgetsData = $this->seoWidgetsPresenter->getDataByLotterySlug(
                $lotterySlug,
                $orderUrl,
                $currencyCode
            );
        } catch (Throwable) {
            return $this->returnResponse([
                'message' => 'Something wrong while loading lottery'
            ]);
        }
        $viewName = $this->seoWidgetsPresenter->getViewNameFromWidgetType($widgetType);

        return $this->returnTwigView($viewName, $seoWidgetsData);
    }
}
