<?php
/**
 * Model
 *
 */
namespace WSSDK\Model;

/**
 * Exeption for faild validation of models fields
 */
class ModelValidationException extends \Exception { }

/**
 * Models class providing common functionailty for
 * validation and serialization
 *
 * Review the Web Service API documentation for specifics on input fields
 */
abstract class BaseModel {

	/**
	 * set of flieds that represent the request fileds for an api call
	 * @var object
	 */
	protected $fields;
	/**
	 * fields that can not be null
	 * @see  self::validate() used by the self::validate
	 * @var array
	 */
	protected $required;

	/**
	 * checks that all Mandatory fields are set
	 * @throws ModelValidationException if mandatory field is not set on model
	 * @return void
	 */
	protected function validate() {

		if (!is_null($this->required)){
			$invalid = [];
			foreach ($this->required as $key) {
				if (!property_exists($this->fields, $key) || is_null($this->fields->{$key})){
					$invalid[] = $key;
				}
			}

			if (count($invalid) > 0){
				throw new ModelValidationException(get_class($this) . ': missing fields ["'. join("\", \"", $invalid) .'"] are required', 0);
			}
		}

	}

	/**
	 * validates the model and return a url form encoded string of set fields
	 * @return String
	 */
	protected function formSerialize() {
		//$this->validate();
		
		$Fields = [];
		foreach ( $this->fields as $Key => $Value ) {
			if($Value !== null && $Value !== '') {
				$Fields[$Key] = $Value;
			} 	
		}
		return http_build_query( $Fields );
	}

	/**
	 * get
	 * @return String[]
	 */
	public function getFieldNames(){
		return array_keys((array)$this->fields);
	}

	/**
	 * get
	 * @return String[]
	 */
	public function getMandatoryFields(){
		return $this->required;
	}

	/**
	 * This method takes a model and after validating it will generate a url encoded String
	 * @return String         A form url encoded string of post parameters representing this model
	 */
	public function Serialize(){
		$this->validate();
		return $this->formSerialize();
	}

	/**
	 * [isDateValid description]
	 * @param  [type]  $date   [description]
	 * @param  string  $format [description]
	 * @return boolean         [description]
	 */
	static function isDateValid($date, $format = "Y-m-d"){
		$dt = \DateTime::createFromFormat($format, $date);
 		return $dt ==! false && !array_sum($dt->getLastErrors());
	}

};

