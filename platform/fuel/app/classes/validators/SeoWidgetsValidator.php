<?php

namespace Validators;

use Exception;
use Fuel\Core\Input;
use Helpers\SanitizerHelper;
use Repositories\Orm\CurrencyRepository;
use Repositories\WhitelabelLotteryRepository;
use Validators\Rules\Currency;
use Validators\Rules\Slug;
use Validators\Rules\Url;

class SeoWidgetsValidator extends Validator
{
    protected static string $method = Validator::GET;

    /** Use camelCase */
    private const WIDGET_TYPES = [
      'pickNumbers',
    ];
    public function __construct(
        private WhitelabelLotteryRepository $whitelabelLotteryRepository,
        private CurrencyRepository $currencyRepository,
    ) {
        parent::__construct();
    }

    private function sanitizeInputData(): void
    {
        $lotterySlug = $this->input['lotterySlug'] ?? Input::get('lotterySlug', '');
        $widgetType = $this->input['widgetType'] ?? Input::get('widgetType', '');
        $orderUrl = $this->input['orderUrl'] ?? Input::get('orderUrl', '');
        $currencyCode = $this->input['currencyCode'] ?? Input::get('currencyCode', '');

        $this->setCustomInput([
            'lotterySlug' => SanitizerHelper::sanitizeSlug($lotterySlug),
            'widgetType' => SanitizerHelper::sanitizeSlug($widgetType),
            'orderUrl' => $orderUrl,
            'currencyCode' => SanitizerHelper::sanitizeSlug($currencyCode),
        ]);
    }

    protected function buildValidation(...$args): void
    {
        $this->sanitizeInputData();

        $this->addFieldRule(Slug::build('lotterySlug', 'lotterySlug'));
        $this->addFieldRule(Slug::build('widgetType', 'widgetType'));
        $this->addFieldRule(Url::build('orderUrl', 'orderUrl'));
        $this->addFieldRule(Currency::build('currencyCode', 'currencyCode'));
    }

    protected function extraChecks(...$args): bool
    {
        [$lotterySlug, $widgetType, $currencyCode] = $this->getProperties(['lotterySlug', 'widgetType', 'currencyCode']);

        $isLotteryInvalid = $this->whitelabelLotteryRepository->isDisabledForCurrentWhitelabelByLotterySlug($lotterySlug);
        if ($isLotteryInvalid) {
            $this->setErrors(['errors' => 'Lottery is not available for this whitelabel']);
            return false;
        }

        $isWidgetTypeInvalid = !in_array($widgetType, self::WIDGET_TYPES);
        if ($isWidgetTypeInvalid) {
            $this->setErrors(['errors' => "Provided widgetType property: $widgetType is wrong."]);
            return false;
        }

        $currencyNotFound = !empty($currencyCode) && empty($this->currencyRepository->findOneByCode($currencyCode));
        if ($currencyNotFound) {
            $this->setErrors([
                'errors' => "Currency $currencyCode not exists",
            ]);
            return false;
        }

        return true;
    }
}
