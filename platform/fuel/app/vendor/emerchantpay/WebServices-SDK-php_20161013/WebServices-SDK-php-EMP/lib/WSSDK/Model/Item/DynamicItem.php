<?php
/**
 * Abstract class used by the various incarnations of Dynamic Items
 */

namespace WSSDK\Model\Item;

/**
 * Abstract class used by the various incarnations of Dynamic Items
 */
abstract class DynamicItem extends PredefinedItem {

	/**
	 * "0" – Item is a Physical Product
	 * @see  setDigital() setDigital()
	 */
	const PHYSICAL_PRODUCT = 0;
	/**
	 * "1" – Item is a Digital Product
	 * @see  setDigital() setDigital()
	 */
	const DIGITAL_PRODUCT = 1;

	/**
	 * build new Dynamic item
	 * @param String Option code to set with constructor
	 */
	public function __construct($code = null){

		parent::__construct($code);

		$extend_required = [
			'item_XX_name'
        ];
		$extend_fields = [
			'item_XX_name' => null,
			'item_XX_description' => null,
			'item_XX_digital' => null,
			'item_XX_rebill' => null // defaults to zero in api
        ];

		foreach ($extend_required as $value) {
			$this->required[] = $value;
		}
		foreach ($extend_fields as $key => $value) {
			$this->fields->{$key} = $value;
		}

		// not predefinined
		$this->fields->item_XX_predefined = 0;
	}

	/* SETTERS */
	/**
	 * set
	 * @param String
	 */
	public function setName ($value){
		$this->fields->item_XX_name = $value;
	}
	/**
	 * set
	 * @param String
	 */
	public function setDescription ($value){
		$this->fields->item_XX_description = $value;
	}
	/**
	 * Use the class constants (PHYSICAL_PRODUCT or DIGITAL_PRODUCT) to chose a value
	 * @see self::PHYSICAL_PRODUCT PHYSICAL_PRODUCT
	 * @see self::DIGITAL_PRODUCT DIGITAL_PRODUCT
	 * @param Integer
	 */
	public function setProductType ($value){
		$this->fields->item_XX_digital = $value;
	}

	/* GETTER */
	/**
	 * get
	 * @return String
	 */
	public function getName (){
		return $this->fields->item_XX_name;
	}
	/**
	 * get
	 * @return String
	 */
	public function getDescription (){
		return $this->fields->item_XX_description;
	}
	/**
	 * get
	 * @return String
	 */
	public function getProductType (){
		return $this->fields->item_XX_digital;
	}

}

