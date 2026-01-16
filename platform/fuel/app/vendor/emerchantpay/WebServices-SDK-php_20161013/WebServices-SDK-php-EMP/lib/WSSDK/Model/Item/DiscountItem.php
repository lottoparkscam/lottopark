<?php

namespace WSSDK\Model\Item;

class DiscountItem extends Item {

	private $currencyCache = [];
	protected $required = [
		'item_XX_discount',
		'item_XX_name'
    ];

	public function __construct(){

		$this->fields = (object) [
			'item_XX_discount' => 1,
			'item_XX_name' => null,
			'item_XX_description' => null
        ];
	}

	/* SETTERS */
	public function setUnitPrice ($currency, $value){
		if ($value > 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Item ' . $this->getItemNumber() . ']: Discount value must be a negative value.', 1);
		}
		$this->currencyCache[$currency] = $value;
		$this->fields->{"item_XX_unit_price_$currency"} = $value;
	}
	public function setName ($value){
		$this->fields->item_XX_name = $value;
	}
	public function setDescription ($value){
		$this->fields->item_XX_description = $value;
	}

	/* GETTER */
	public function getUnitPrice ($currency){
		return $this->fields->{"item_XX_unit_price_$currency"};
	}
	public function getName (){
		return $this->fields->item_XX_name;
	}
	public function getDescription (){
		return $this->fields->item_XX_description;
	}


	protected function validate(){
		parent::validate();
		if (count($this->currencyCache) === 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Item ' . $this->getItemNumber() . ']: Unit price is required.');
		}
	}

}

