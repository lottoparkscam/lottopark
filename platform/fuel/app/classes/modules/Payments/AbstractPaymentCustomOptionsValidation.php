<?php

namespace Modules\Payments;

use Container;
use Forms_Whitelabel_Payment_ShowData;
use Fuel\Core\Input;
use Fuel\Core\Validation;
use Models\WhitelabelPaymentMethod;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Validator_Validator;
use Webmozart\Assert\Assert;
use Wrappers\Decorators\ConfigContract;

/**
 * @deprecated - to be handled by generic approach for handling payment`s routes.
 *
 * Class JetonPaymentValidationForm
 * Generic class for Modern Facade Payments
 */
abstract class AbstractPaymentCustomOptionsValidation extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    public const PAYMENT_SLUG = '';
    public const DEFAULT_RULES = 'trim|min_length[3]|max_length[100]';
    public const WHITELABEL_PAYMENT_METHOD_ID_POSITION_IN_QUERY = 5;
    public const MANAGER_PREFIX_CHARS_NUMBER_IN_HOSTNAME = 8;

    protected PaymentFacadeContract $facade;
    protected ConfigContract $defaultConfig;
    protected WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;

    public function __construct()
    {
        Assert::notEmpty(static::PAYMENT_SLUG, 'Missing PAYMENT_SLUG in Form');
        $this->facade = Container::getPaymentFacade(static::PAYMENT_SLUG);
        $this->defaultConfig = Container::get(ConfigContract::class);
        $this->whitelabelPaymentMethodRepository = Container::get(WhitelabelPaymentMethodRepository::class);
    }

    /**
     * This is really minimal, common validation rule.
     * Feel free to write Your own validator!
     *
     * @return Validation
     */
    public function build_validation(): Validation
    {
        $validation = Validation::forge(static::PAYMENT_SLUG);
        $customizableFields = $this->facade->getCustomizableOptions();

        foreach ($customizableFields as $key) {
            $validation->add_field("input.$key", _($key), static::DEFAULT_RULES);
        }

        $validation->set_message('required', _('The field :label cannot be empty'));
        $validation->set_message('min_length', _('The field :label is too short'));
        $validation->set_message('max_length', _('The field :label is too long'));

        return $validation;
    }

    /**
     * Some copy&paste..
     * Data visible in empire admin panel on edit.
     * Add anything You need in view..
     *
     * @param array|null $data
     * @param array|null $errors
     * @return array
     */
    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array {
        $customizableFields = $this->facade->getCustomizableOptions();
        $config = $this->facade->getConfig();
        $queryString = Input::query_string();

        $chunks = explode('/', $queryString);
        if (isset($chunks[self::WHITELABEL_PAYMENT_METHOD_ID_POSITION_IN_QUERY])) {
            $whitelabelPaymentMethodId = (int)$chunks[self::WHITELABEL_PAYMENT_METHOD_ID_POSITION_IN_QUERY];
            /** @var WhitelabelPaymentMethod $whitelabelPaymentMethod */
            $whitelabelPaymentMethod = $this->whitelabelPaymentMethodRepository->getById($whitelabelPaymentMethodId);
        }

        $paymentMethodData = [];
        if (!empty($whitelabelPaymentMethod)) {
            $paymentMethodData = !empty($whitelabelPaymentMethod->data_json) ? array_filter($whitelabelPaymentMethod->data_json) : [];
        }

        $config = array_merge($config, $paymentMethodData);

        foreach ($customizableFields as $key) {
            $inputName = "input.$key";
            $data[$key] = Input::post($inputName) ?? $config[$key];
        }

        $data['title'] = sprintf('%s customizable options', ucfirst(static::PAYMENT_SLUG));
        $data['sub_title'] = _('Enter merchant credentials for gateways');

        # here we fetch default hints for placeholders (from default FuelConfig)
        $data['help'] = $this->defaultConfig->get(sprintf('payments.%s', static::PAYMENT_SLUG));

        return $data;
    }

    /**
     * This is what will be parsed and stored in config..
     *
     * @param Validation|null $additional_values_validation
     * @return array
     */
    public function get_data(?Validation $additional_values_validation): array
    {
        $customizableFields = $this->facade->getCustomizableOptions();

        $data = [];

        foreach ($customizableFields as $key) {
            $data[$key] = $additional_values_validation->validated("input.$key");
        }

        return $data;
    }
}
