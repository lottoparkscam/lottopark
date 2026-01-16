<?php

namespace Validators\Rules;

use Helpers\TypeHelper;
use LogicException;
use Repositories\Orm\CurrencyRepository;

class CurrencyCode extends Rule
{
    protected string $type = TypeHelper::STRING;

    private CurrencyRepository $currencyRepository;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
    }

    public function configure(CurrencyRepository $currencyRepository): self
    {
        $this->currencyRepository = $currencyRepository;
        return $this;
    }

    /**
     * @throws LogicException when rule has not been configured with currency repository
     */
    public function applyRules(): void
    {
        if (empty($this->currencyRepository)) {
            throw new LogicException('CurrencyCode rule has to be configured with currency repository');
        }

        $currencies = $this->currencyRepository->getAllCodes();

        $this->field
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_string', 'alpha')
            ->add_rule('exact_length', 3)
            ->add_rule('match_collection', $currencies, true)
            ->set_error_message('exact_length', 'The field ' . $this->label . ' must contain exactly 3 characters.');
    }
}
