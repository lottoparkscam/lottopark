<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class AirlineDataGW41Leg {

	private $travel_date;
	private $city_of_origin_airport_code;
	private $carrier_code;
	private $service_class;
	private $city_of_destination_airport_code;
	private $fare_basis_code;

	public function __construct(
		$travel_date,
		$city_of_origin_airport_code,
		$carrier_code,
		$service_class = null,
		$city_of_destination_airport_code = null,
		$fare_basis_code = null
	){
		$this->setTravelDate($travel_date);
		$this->setCityOfOriginAirportCode($city_of_origin_airport_code);
		$this->setCarrierCode($carrier_code);
		$this->setServiceClass($service_class);
		$this->setCityOfDestinationAirportCode($city_of_destination_airport_code);
		$this->setFareBasisCode($fare_basis_code);
	}

		/* SETTER */
	public function setTravelDate ($value){
		if (!Model\BaseModel::isDateValid($value)){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": Date must match format YYYY-MM-DD", 1);
		}
		$this->travel_date = $value;
	}
	public function setCityOfOriginAirportCode ($value){
		$this->city_of_origin_airport_code = $value;
	}
	public function setCarrierCode ($value){
		$this->carrier_code = $value;
	}
	public function setServiceClass ($value){
		$this->service_class = $value;
	}
	public function setCityOfDestinationAirportCode($value){
		$this->city_of_destination_airport_code = $value;
	}
	public function setFareBasisCode($value){
		$this->fare_basis_code = $value;
	}

		/* GETTER */
	public function getTravelDate (){
		return $this->travel_date;
	}
	public function getCityOfOriginAirportCode (){
		return $this->city_of_origin_airport_code;
	}
	public function getCarrierCode (){
		return $this->carrier_code;
	}
	public function getServiceClass (){
		return $this->service_class;
	}
	public function getCityOfDestinationAirportCode(){
		return $this->city_of_destination_airport_code;
	}
	public function getFareBasisCode(){
		return $this->fare_basis_code;
	}

}

class AirlineDataGW41 extends AirlineData {

	protected $required = [
		'airline_data',
		'ad_passenger_name',
		'ad_flight_number',
		'ad_travel_date',
		'ad_city_of_origin_airport_code',
		'ad_carrier_code',
		'ad_travel_agency_code',
		'ad_travel_agency_name',
		'ad_ticket_number'
    ];

	private $legs = [];

	public function __construct(){
		$this->fields = (object) [
			'airline_data' => 1,
			'ad_passenger_name' => null,
			'ad_flight_number' => null,
			'ad_travel_date' => null,
			'ad_city_of_origin_airport_code' => null,
			'ad_carrier_code' => null,
			'ad_service_class' => null,
			'ad_city_of_destination_airport_code' => null,
			'ad_fare_basis_code' => null,
			'ad_travel_agency_code' => null,
			'ad_travel_agency_name' => null,
			'ad_ticket_number' => null
        ];
	}

	protected function validate() {
		$travel_date = [];
		$city_of_origin_airport_code = [];
		$carrier_code = [];
		$service_class = [];
		$city_of_destination_airport_code = [];
		$fare_basis_code = [];

		foreach ($this->legs as $key => $leg) {
			$travel_date[] = $leg->getTravelDate();
			$city_of_origin_airport_code[] = $leg->getCityOfOriginAirportCode();
			$carrier_code[] = $leg->getCarrierCode();
			$service_class[] = $leg->getServiceClass();
			$city_of_destination_airport_code[] = $leg->getCityOfDestinationAirportCode();
			$fare_basis_code[] = $leg->getFareBasisCode();
		}

		$this->fields->ad_travel_date = implode("|", $travel_date);
		$this->fields->ad_city_of_origin_airport_code = implode("|", $city_of_origin_airport_code);
		$this->fields->ad_carrier_code = implode("|", $carrier_code);
		$this->fields->ad_service_class = implode("|", $service_class);
		$this->fields->ad_city_of_destination_airport_code = implode("|", $city_of_destination_airport_code);
		$this->fields->ad_fare_basis_code = implode("|", $fare_basis_code);

		parent::validate();
	}

	public function addLeg(AirlineDataGW41Leg $value){
		if(Count($this->legs) >= 4){
			throw new \Exception("A maximum of 4 legs can be added to a flight", 1);
		}
		$this->legs[] = $value;

	}

	/* SETTER */
	public function setPassengerName ($value){
		$this->fields->ad_passenger_name = $value;
	}
	public function setFlightNumber ($value){
		$this->fields->ad_flight_number = $value;
	}
	public function setTravelAgencyCode ($value){
		$this->fields->ad_travel_agency_code = $value;
	}
	public function setTravelAgencyName ($value){
		$this->fields->ad_travel_agency_name = $value;
	}
	public function setTicketNumber($value){
		$this->fields->ad_ticket_number = $value;
	}

	/* GETTERS */
	public function getPassengerName (){
		return $this->fields->ad_passenger_name;
	}
	public function getFlightNumber (){
		return $this->fields->ad_flight_number;
	}
	public function getTravelAgencyCode (){
		return $this->fields->ad_travel_agency_code;
	}
	public function getTravelAgencyName (){
		return $this->fields->ad_travel_agency_name;
	}
	public function getTicketNumber(){
		return $this->fields->ad_ticket_number;
	}
	public function getLeg($number = null){
		if (is_null($number)){
			return $this->legs;
		} else {
			return $this->legs[$number];
		}
	}

}