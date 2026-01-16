<?php

namespace WSSDK\Model\Item;

class PredefinedItem extends Item {

	protected $required = [
		'item_XX_code',
		'item_XX_qty',
		'item_XX_predefined',
    ];

	public function __construct($code = null){

		$this->fields = (object) [
			'item_XX_code' => $code,
			'item_XX_qty' => null,
			'item_XX_predefined' => 1,
			'item_XX_optional' => null,
			'item_XX_pass_through' => null
        ];
	}

	/* SETTERS */
	public function setCode ($value){
		$this->fields->item_XX_code = $value;
	}
	public function setQuantity ($value){
		$this->fields->item_XX_qty = $value;
	}
	public function setOptional ($value){
		$this->fields->item_XX_optional = $value;
	}
	public function setPassThrough ($value){
		$this->fields->item_XX_pass_through = $value;
	}

	/* GETTER */
	public function getCode (){
		return $this->fields->item_XX_code;
	}
	public function getQuantity (){
		return $this->fields->item_XX_qty;
	}
	public function isOptional (){
		return $this->fields->item_XX_optional;
	}
	public function getPassThrough (){
		return $this->fields->item_XX_pass_through;
	}

}

