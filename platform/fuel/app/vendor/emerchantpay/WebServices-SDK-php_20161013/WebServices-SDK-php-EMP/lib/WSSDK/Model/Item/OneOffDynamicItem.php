<?php

namespace WSSDK\Model\Item;

/**
 * This Dynamic Item represents a item with an item_X_rebill value of 0
 */
class OneOffDynamicItem extends DynamicItem {

	private $currencyCache = [];
	public function __construct(){
		parent::__construct();
	}

	/* SETTERS */
		public function setUnitPrice ($currency, $value){
		$this->currencyCache[$currency] = $value;
		$this->fields->{"item_XX_unit_price_$currency"} = $value;
	}

	/* GETTER */
		public function getUnitPrice ($currency){
		return $this->fields->{"item_XX_unit_price_$currency"};
	}

	protected function validate(){
		parent::validate();
		if (count($this->currencyCache) === 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Item ' . $this->getItemNumber() . ']: Unit price is required.');
		}
	}

}

