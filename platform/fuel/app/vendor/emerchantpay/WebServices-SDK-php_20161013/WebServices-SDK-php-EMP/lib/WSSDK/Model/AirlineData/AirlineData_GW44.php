<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

class AirlineDataGW44Leg {

	private $isArray = [
		'ad_conjunction_ticket',
		'ad_exchange_ticket',
		'ad_coupon_number',
		'ad_service_class',
		'ad_travel_date',
		'ad_carrier_code',
		'ad_stopover_code',
		'ad_city_of_origin_airport_code',
		'ad_city_of_destination_airport_code',
		'ad_flight_number',
		'ad_departure_time',
		'ad_departure_time_segment',
		'ad_arrival_time',
		'ad_arrival_time_segment',
		'ad_fare_basis_code',
		'ad_fare',
		'ad_taxes',
		'ad_fee',
		'ad_endorsement_or_restrictions'
    ];

	public function __construct(){

		$this->fields = (object) [
			'ad_restricted_ticket_indicator' => null,
			'ad_passenger_name' => null,
			'ad_ticket_number' => null,
			'ad_issuing_carrier' => null,
			'ad_total_fare' => null,
			'ad_total_taxes' => null,
			'ad_total_fee' => null,
			'ad_conjunction_ticket' => [],
			'ad_exchange_ticket' => [],
			'ad_coupon_number' => [],
			'ad_service_class' => [],
			'ad_travel_date' => [],
			'ad_carrier_code' => [],
			'ad_stopover_code' => [],
			'ad_city_of_origin_airport_code' => [],
			'ad_city_of_destination_airport_code' => [],
			'ad_flight_number' => [],
			'ad_departure_time' => [],
			'ad_departure_time_segment' => [],
			'ad_arrival_time' => [],
			'ad_arrival_time_segment' => [],
			'ad_fare_basis_code' => [],
			'ad_fare' => [],
			'ad_taxes' => [],
			'ad_fee' => [],
			'ad_endorsement_or_restrictions' => []
        ];

	}

	public function validate(){

		$invalid = [];
		foreach ($this->isArray as $key) {
			if (count($this->fields->{$key}) !== 0 && count($this->fields->{$key}) > 4){
				$invalid[] = $key;
			}
		}

		if (Count($invalid) > 0){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ': The following fields ["'. join("\", \"", $invalid) .'"] must ba an array with a max length of 4', 0);
		}

		return true;
	}

	/* SETTER */

	/**
	 * Indication of whether the
     * ticket is refundable
     * 0 – Non Refundable
     * 1 - Refundable
	 * @param number $value
	 */
	public function setRestrictedTicketIndicator($value) {
		$this->fields->ad_restricted_ticket_indicator = $value;
	}
	public function setPassengerName($value) {
		$this->fields->ad_passenger_name = $value;
	}
	public function setTicketNumber($value) {
		$this->fields->ad_ticket_number = $value;
	}
	public function setIssuingCarrier($value) {
		$this->fields->ad_issuing_carrier = $value;
	}
	public function setTotalFare($value) {
		$this->fields->ad_total_fare = $value;
	}
	public function setTotalTaxes($value) {
		$this->fields->ad_total_taxes = $value;
	}
	public function setTotalFee($value) {
		$this->fields->ad_total_fee = $value;
	}
	public function setConjunctionTicket($tripValues) {
		$this->fields->ad_conjunction_ticket = $tripValues;
	}
	public function setExchangeTicket($tripValues) {
		$this->fields->ad_exchange_ticket = $tripValues;
	}
	public function setCouponNumber($tripValues) {
		$this->fields->ad_coupon_number = $tripValues;
	}
	public function setServiceCalss($tripValues) {
		$this->fields->ad_service_class = $tripValues;
	}
	public function setTravelDate($tripValues) {
		foreach ($tripValues as $key => $value) {
			if (!is_null($value) && !Model\BaseModel::isDateValid($value)){
				throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setTravelDate must match format YYYY-MM-DD", 1);
			}
		}
		$this->fields->ad_travel_date = $tripValues;
	}
	public function setCarrierCode($tripValues) {
		$this->fields->ad_carrier_code = $tripValues;
	}
	public function setStapoverCode($tripValues) {
		$this->fields->ad_stopover_code = $tripValues;
	}
	public function setCityOfOriginAirportCode($tripValues) {
		$this->fields->ad_city_of_origin_airport_code = $tripValues;
	}
	public function setCityOfDestinationAirportCode($tripValues) {
		$this->fields->ad_city_of_destination_airport_code = $tripValues;
	}
	public function setFlightNumber($tripValues) {
		$this->fields->ad_flight_number = $tripValues;
	}
	/**
	 * [setDepartureTime description]
	 * @param array<String> $value  12 Hour time eg 1:25
	 * @param array<String> $period AM or PM
	 */
	public function setDepartureTime($tripValues, $tripPeriods) {

		foreach ($tripValues as $key => $value) {
			if (!is_null($value) && !Model\BaseModel::isDateValid($value, 'h:i')){
				throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setDepartureTime value: $value must be a valid 12 hour time eg: 01:00", 1);
			}
		}
		foreach ($tripPeriods as $key => $period) {
			if (!is_null($value) && $period !== 'AM' && $period !== 'PM'){
				throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setDepartureTime period: $period must be either AM or PM", 1);
			}
		}

		$this->fields->ad_departure_time = $tripValues;
		$this->fields->ad_departure_time_segment = $tripPeriods;
	}
	public function setArrivalTime($tripValues, $tripPeriods) {

		foreach ($tripValues as $key => $value) {
			if (!is_null($value) && !Model\BaseModel::isDateValid($value, 'h:i')){
				throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setDepartureTime value: $value must be a valid 12 hour time eg: 01:00", 1);
			}
		}
		foreach ($tripPeriods as $key => $period) {
			if (!is_null($value) && $period !== 'AM' && $period !== 'PM'){
				throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setDepartureTime period: $period must be either AM or PM", 1);
			}
		}

		$this->fields->ad_arrival_time = $tripValues;
		$this->fields->ad_arrival_time_segment = $tripPeriods;
	}
	public function setFareBasisCode($tripValues) {
		$this->fields->ad_fare_basis_code = $tripValues;
	}
	public function setFare($tripValues) {
		$this->fields->ad_fare = $tripValues;
	}
	public function setTaxes($tripValues) {
		$this->fields->ad_taxes = $tripValues;
	}
	public function setFee($tripValues) {
		$this->fields->ad_fee = $tripValues;
	}
	public function setEndorsementOrRestrictions($tripValues) {
		$this->fields->ad_endorsement_or_restrictions = $tripValues;
	}

	/* GETTER */
	/**
	 * Returns an object of fields and thier values
	 * @return [type] [description]
	 */
	public function getAllData(){
		return $this->fields;
	}
	public function getRestrictedTicketIndicator() {
		return $this->fields->ad_restricted_ticket_indicator;
	}
	public function getPassengerName() {
		return $this->fields->ad_passenger_name;
	}
	public function getTicketNumber() {
		return $this->fields->ad_ticket_number;
	}
	public function getIssuingCarrier() {
		return $this->fields->ad_issuing_carrier;
	}
	public function getTotalFare() {
		return $this->fields->ad_total_fare;
	}
	public function getTotalTaxes() {
		return $this->fields->ad_total_taxes;
	}
	public function getTotalFee() {
		return $this->fields->ad_total_fee;
	}
	public function getConjunctionTicket() {
		return $this->fields->ad_conjunction_ticket;
	}
	public function getExchangeTicket() {
		return $this->fields->ad_exchange_ticket;
	}
	public function getCouponNumber() {
		return $this->fields->ad_coupon_number;
	}
	public function getServiceCalss() {
		return $this->fields->ad_service_class;
	}
	public function getTravelDate() {
		return $this->fields->ad_travel_date;
	}
	public function getCarrierCode() {
		return $this->fields->ad_carrier_code;
	}
	public function getStapoverCode() {
		return $this->fields->ad_stopover_code;
	}
	public function getCityOfOriginAirportCode() {
		return $this->fields->ad_city_of_origin_airport_code;
	}
	public function getCityOfDestinationAirportCode() {
		return $this->fields->ad_city_of_destination_airport_code;
	}
	public function getFlightNumber() {
		return $this->fields->ad_flight_number;
	}
	public function getDepartureTime() {
		return (object) [
			'times' => $this->fields->ad_departure_time = $tripValues,
			'periods' => $this->fields->ad_departure_time_segment = $tripPeriods
        ];
	}
	public function getArrivalTime($tripValues, $tripPeriods) {
		return (object) [
			'times' => $this->fields->ad_arrival_time = $tripValues,
			'periods' => $this->fields->ad_arival_time_segment = $tripPeriods
        ];
	}
	public function getFareBasisCode() {
		return $this->fields->ad_fare_basis_code;
	}
	public function getFare() {
		return $this->fields->ad_fare;
	}
	public function getTaxes() {
		return $this->fields->ad_taxes;
	}
	public function getFee() {
		return $this->fields->ad_fee;
	}
	public function getEndorsementOrRestrictions() {
		return $this->fields->ad_endorsement_or_restrictions;
	}

}

class AirlineDataGW44 extends AirlineData {

	protected $required = [
		'airline_data'
    ];

	private $legs = [];

	public function __construct(){
		$this->fields = (object) [
			/*[Leg][Trip]*/
			/*[ ][ ]*/'airline_data' => 1,
			/*[X][ ]*/'ad_restricted_ticket_indicator' => null,
			/*[X][ ]*/'ad_passenger_name' => null,
			/*[ ][ ]*/'ad_issue_date' => null,
			/*[ ][ ]*/'ad_travel_agency_name' => null,
			/*[ ][ ]*/'ad_travel_agency_code' => null,
			/*[X][ ]*/'ad_ticket_number' => null,
			/*[ ][ ]*/'ad_customer_code' => null,
			/*[X][ ]*/'ad_issuing_carrier' => null,
			/*[X][ ]*/'ad_total_fare' => null,
			/*[X][ ]*/'ad_total_taxes' => null,
			/*[X][ ]*/'ad_total_fee' => null,
			/*[X][X]*/'ad_conjunction_ticket' => null,
			/*[X][X]*/'ad_exchange_ticket' => null,
			/*[X][X]*/'ad_coupon_number' => null,
			/*[X][X]*/'ad_service_class' => null,
			/*[X][X]*/'ad_travel_date' => null,
			/*[X][X]*/'ad_carrier_code' => null,
			/*[X][X]*/'ad_stopover_code' => null,
			/*[X][X]*/'ad_city_of_origin_airport_code' => null,
			/*[X][X]*/'ad_city_of_destination_airport_code' => null,
			/*[X][X]*/'ad_flight_number' => null,
			/*[X][X]*/'ad_departure_time' => null,
			/*[X][X]*/'ad_departure_time_segment' => null,
			/*[X][X]*/'ad_arrival_time' => null,
			/*[X][X]*/'ad_arrival_time_segment' => null,
			/*[X][X]*/'ad_fare_basis_code' => null,
			/*[X][X]*/'ad_fare' => null,
			/*[X][X]*/'ad_taxes' => null,
			/*[X][X]*/'ad_fee' => null,
			/*[X][X]*/'ad_endorsement_or_restrictions' => null
        ];
	}

	protected function validate() {

		$tempFields = (object) [];

		foreach ($this->legs as $leg) {
			$fields = $leg->getAllData();

			foreach ($fields as $key => $value) {

				if(!property_exists($tempFields, $key)){
					$tempFields->{$key} = [];
				}

				if (!is_null($value)){
					if (is_array($value)){
						if (count($value) !== 0){
							$tempFields->{$key}[] = implode('##', $value);
						} 
					} else {
						$tempFields->{$key}[] = $value;
					}
				}

			}

		}

		foreach ($tempFields as $key => $value) {
			if (is_array($value) && count($value) !== 0){
				$this->fields->{$key} = implode('|', $value);
			}
		}

		parent::validate();
	}

	public function addLeg(AirlineDataGW44Leg $value){
		if(Count($this->legs) >= 4){
			throw new \Exception("A maximum of 4 legs can be added to a flight", 1);
		}

		if($value->validate()){
			$this->legs[] = $value;
		}

	}

	/* SETTER */
	public function setIssueDate ($value){
		if (!Model\BaseModel::isDateValid($value)){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": setIssueDate must match format YYYY-MM-DD", 1);
		}
		$this->fields->ad_issue_date = $value;
	}
	public function setTravelAgencyName ($value){
		$this->fields->ad_travel_agency_name = $value;
	}
	public function setTravelAgencyCode ($value){
		$this->fields->ad_travel_agency_code = $value;
	}
	public function setCustomerCode ($value){
		$this->fields->ad_customer_code = $value;
	}

	/* GETTER */
	public function getIssueDate (){
		return $this->fields->ad_issue_date;
	}
	public function getTravelAgencyName (){
		return $this->fields->ad_travel_agency_name;
	}
	public function getTravelAgencyCode (){
		return $this->fields->ad_travel_agency_code;
	}
	public function getCustomerCode (){
		return $this->fields->ad_customer_code;
	}
	public function getLeg($number = null){
		if (is_null($number)){
			return $this->legs;
		} else {
			return $this->legs[$number];
		}
	}
}