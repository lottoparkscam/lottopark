<?php

namespace WSSDK\Model\Item;

/**
 * This Dynamic Item represents a item with an item_X_rebill value of 2
 */
class MerchantManagedRebillDynamicItem extends OneOffDynamicItem {

	private $currencyCache = [];
	public function __construct(){

		parent::__construct();

		// not rebill type
		$this->fields->item_XX_rebill = 2;
	}

}

/**
 * This Dynamic Item represents a item with an item_X_rebill value of 1
 */
class ManagedRebillDynamicItem extends DynamicItem {

	private $rebillCurrencyCache = [];
	private $initialCurrencyCache = [];

	public function __construct($code = null){

		parent::__construct($code);

		$extend_required = [
			'item_XX_rebill_period'
        ];
		$extend_fields = [
			'item_XX_rebill_period' => null,
			'item_XX_initial_period' => null,
			'item_XX_rebill_count' => null
        ];

		foreach ($extend_required as $value) {
			$this->required[] = $value;
		}
		foreach ($extend_fields as $key => $value) {
			$this->fields->{$key} = $value;
		}

		// not rebill type
		$this->fields->item_XX_rebill = 1;
	}

	/* SETTERS */
	public function setRebillPeriod ($value){
		$this->fields->item_XX_rebill_period = $value;
	}
	public function setRebillPrice ($currency, $value){
		$this->rebillCurrencyCache[$currency] = $value;
		$this->fields->{"item_XX_rebill_price_$currency"} = $value;
	}
	public function setInitialPeriod ($value){
		$this->fields->item_XX_initial_period = $value;
	}
	public function setInitialPrice ($currency, $value){
		$this->initialCurrencyCache[$currency] = $value;
		$this->fields->{"item_XX_initial_price_$currency"} = $value;
	}
	public function setRebillCount ($value){
		$this->fields->item_XX_rebill_count = $value;
	}

	/* GETTER */
	public function getRebillPeriod (){
		return $this->fields->item_XX_rebill_period;
	}
	public function getRebillPrice ($currency){
		return $this->fields->{"item_XX_rebill_price_$currency"};
	}
	public function getInitialPeriod (){
		return $this->fields->item_XX_initial_period;
	}
	public function getInitialPrice ($currency){
		return $this->fields->{"item_XX_initial_price_$currency"};
	}
	public function getRebillCount (){
		return $this->fields->item_XX_rebill_count;
	}


	protected function validate(){
		parent::validate();
		if (count($this->rebillCurrencyCache) === 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Item ' . $this->getItemNumber() . ']: Rebill price is required.');
		}

		if (isset($this->fields->item_XX_initial_period) && count($this->initialCurrencyCache) === 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Item ' . $this->getItemNumber() . ']: Initial price is required when initial periond is set.');
		}
	}

}

