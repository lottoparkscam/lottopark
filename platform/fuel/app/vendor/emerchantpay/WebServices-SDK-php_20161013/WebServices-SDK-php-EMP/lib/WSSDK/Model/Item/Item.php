<?php
/**
 * Abstract Items Class
 */

namespace WSSDK\Model\Item;
require_once __DIR__."/PredefinedItem.php";
require_once __DIR__."/DiscountItem.php";
require_once __DIR__."/DynamicItem.php";
require_once __DIR__."/OneOffDynamicItem.php";
require_once __DIR__."/ManagedRebillDynamicItem.php";

use \WSSDK\Model as Model;

/**
 * This abstract item is the bse model for all order items
 */
abstract class Item extends Model\BaseModel {

	/**
	 * the number to use for item fields when serialized to for url encoded string
	 * @var Integer
	 */
	protected $item_number;

	/**
	 * sets the item number in the order
	 * @param Integer the item number for formSerialize to use when encoding
	 */
	public function setItemNumber($value){
		$this->item_number = $value;
	}
	/**
	 * gets the item number in the order
	 * @return Integer
	 */
	public function getItemNumber(){
		return $this->item_number;
	}
	/**
	 * Overidden formSerialize method that makes use of $item_number when serailizing
	 * @return String
	 */
	protected function formSerialize() {
		$encoded = parent::formSerialize();
		return str_replace('XX', $this->item_number, $encoded);
	}

	protected function validate() {

		$invalid = [];
		foreach ($this->required as $key) {
			if (!property_exists($this->fields, $key) || is_null($this->fields->{$key})){
				$invalid[] = $key;
			}
		}

		if (Count($invalid) > 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Item ' . $this->getItemNumber() . ']: missing fields ["'. join("\", \"", $invalid) .'"] are required', 0);
		}
	}

}

