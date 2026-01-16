<?php

namespace WSSDK\Model;

use \WSSDK\Model as Model;

/**
 * 
 */
class AirlineDataUATPLeg extends Model\BaseModel {

	/**
	 * the number to use for leg fields when serialized to for url encoded string
	 * @var Integer
	 */
	protected $leg_number;

	protected $required = [
		'ad_coupon_[LEG]_origin_airport_city_code',
		'ad_coupon_[LEG]_destination_airport_city_code',
		'ad_coupon_[LEG]_stopover_code',
		'ad_coupon_[LEG]_reservation_booking_designator',
		'ad_coupon_[LEG]_carrier',
		'ad_coupon_[LEG]_fare_basis_ticket_designator'
    ];

	public function __construct(){
		$this->fields = (object) [
			'ad_coupon_[LEG]_origin_airport_city_code' => null,
			'ad_coupon_[LEG]_destination_airport_city_code' => null,
			'ad_coupon_[LEG]_stopover_code' => null,
			'ad_coupon_[LEG]_reservation_booking_designator' => null,
			'ad_coupon_[LEG]_carrier' => null,
			'ad_coupon_[LEG]_fare_basis_ticket_designator' => null
        ];
	}

	/**
	 * sets the leg number in the order
	 * @param Integer the leg number for formSerialize to use when encoding
	 */
	public function setLegNumber($value){
		$this->leg_number = $value;
	}
	/**
	 * gets the leg number in the order
	 * @return Integer
	 */
	public function getLegNumber(){
		return $this->leg_number;
	}

	protected function formSerialize() {
		$encoded = parent::formSerialize();
		return str_replace('[LEG]', $this->leg_number, $encoded);
	}

 	protected function validate() {
		try {
			parent::validate();
		} catch (Exception $e) {
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Coupon (Leg) ' . $this->getItemNumber() . ']: missing fields ["'. join("\", \"", $invalid) .'"] are required', 0);
		}
	}

	/* SETTER */
	public function setOriginAirportCityCode ($value){
		$this->fields->{'ad_coupon_[LEG]_origin_airport_city_code'} = $value;
	}
	public function setDestinationAirportCityCode ($value){
		$this->fields->{'ad_coupon_[LEG]_destination_airport_city_code'} = $value;
	}
	public function setStopeoverCode($value){
		$this->fields->{'ad_coupon_[LEG]_stopover_code'} = $value;
	}
	public function setReservationBookingDesignator ($value){
		$this->fields->{'ad_coupon_[LEG]_reservation_booking_designator'} = $value;
	}
	public function setCarrier ($value){
		$this->fields->{'ad_coupon_[LEG]_carrier'} = $value;
	}
	public function setFareBasisTicketDesignator ($value){
		$this->fields->{'ad_coupon_[LEG]_fare_basis_ticket_designator'} = $value;
	}

	/* GETTER */
	public function getOriginAirportCityCode (){
		return $this->fields->{'ad_coupon_[LEG]_origin_airport_city_code'};
	}
	public function getDestinationAirportCityCode (){
		return $this->fields->{'ad_coupon_[LEG]_destination_airport_city_code'};
	}
	public function getStopeoverCode(){
		return $this->fields->{'ad_coupon_[LEG]_stopover_code'};
	}
	public function getReservationBookingDesignator (){
		return $this->fields->{'ad_coupon_[LEG]_reservation_booking_designator'};
	}
	public function getCarrier (){
		return $this->fields->{'ad_coupon_[LEG]_carrier'};
	}
	public function getFareBasisTicketDesignator (){
		return $this->fields->{'ad_coupon_[LEG]_fare_basis_ticket_designator'};
	}

}

class AirlineDataUATPTaxFee extends Model\BaseModel {

	/**
	 * the number to use for TaxFee fields when serialized to for url encoded string
	 * @var Integer
	 */
	protected $taxFee_number;

	protected $required = [
		'ad_tax_[TAXFEE]_fee_type',
		'ad_tax_[TAXFEE]_fee_amount'
    ];

	public function __construct(){
		$this->fields = (object) [
			'ad_tax_[TAXFEE]_fee_type' => null,
			'ad_tax_[TAXFEE]_fee_amount' => null
        ];
	}

	/**
	 * sets the TaxFee number in the order
	 * @param Integer the leg number for formSerialize to use when encoding
	 */
	public function setTaxFeeNumber($value){
		$this->taxFee_number = $value;
	}
	/**
	 * gets the TaxFee number in the order
	 * @return Integer
	 */
	public function getTaxFeeNumber(){
		return $this->taxFee_number;
	}

	protected function formSerialize() {
		$encoded = parent::formSerialize();
		return str_replace('[TAXFEE]', $this->taxFee_number, $encoded);
	}

	protected function validate() {
		try {
			parent::validate();
		} catch (Exception $e) {
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ' [Tax Fee ' . $this->getItemNumber() . ']: missing fields ["'. join("\", \"", $invalid) .'"] are required', 0);
		}
	}

	/* SETTER */
	public function setFeeType ($value){
		$this->fields->{'ad_tax_[TAXFEE]_fee_type'} = $value;
	}
	public function setFeeAmount ($value){
		$this->fields->{'ad_tax_[TAXFEE]_fee_amount'} = $value;
	}

	/* GETTER */
	public function getFeeType (){
		return $this->fields->{'ad_tax_[TAXFEE]_fee_type'};
	}
	public function getFeeAmount (){
		return $this->fields->{'ad_tax_[TAXFEE]_fee_amount'};
	}

}

/**
 * 
 */
class AirlineDataUATP extends Model\BaseModel {

	private $legs = [];
	private $fees = [];

	protected $required = [
		'ad_agent_numeric_code',
		'ad_agent_country_code',
		'ad_ticket_document_number',
		'ad_ticket_electronic',
		'ad_ticket_date_of_issue',
		'ad_passenger_name',
		'ad_customer_file_reference',
		'ad_flight_date',
		'ad_coupon_count',
		'ad_confirmation_information',
		'ad_tax_count'
    ];

	public function __construct(){
		$this->fields = (object) [
			'ad_agent_numeric_code' => null,
			'ad_agent_country_code' => null,
			'ad_ticket_document_number' => null,
			'ad_ticket_electronic' => null,
			'ad_ticket_date_of_issue' => null,
			'ad_passenger_name' => null,
			'ad_customer_file_reference' => null,
			'ad_flight_date' => null,
			'ad_coupon_count' => 0,
			'ad_confirmation_information' => null,
			'ad_tax_count' => 0
        ];
	}

	protected function formSerialize() {

		$legsFormatted = [];
		foreach ($this->legs as $leg) {
			$legsFormatted[] = $leg->Serialize();
		}
		$feesFormatted = [];
		foreach ($this->fees as $fee) {
			$feesFormatted[] = $fee->Serialize();
		}

		$encoded = parent::formSerialize();

		return $encoded . implode('&', $legsFormatted) . implode('&', $feesFormatted);

	}

	/* SETTER */
	public function setAgentNumericCode ($value){
		$this->fields->ad_agent_numeric_code = $value;
	}
	public function setAgentCountryCode ($value){
		$this->fields->ad_agent_country_code = $value;
	}
	public function setTicketDocumentNumber ($value){
		$this->fields->ad_ticket_document_number = $value;
	}
	public function setIsTicketElectronic($value){
		$this->fields->ad_ticket_electronic = $value;
	}
	public function setTicketDateOfIssue ($value){
		if (!Model\BaseModel::isDateValid($value)){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": Date $value must match format YYYY-MM-DD", 1);
		}
		$this->fields->ad_ticket_date_of_issue = $value;
	}
	public function setPassengerName ($value){
		$this->fields->ad_passenger_name = $value;
	}
	public function setCustomerFileReference ($value){
		$this->fields->ad_customer_file_reference = $value;
	}
	public function setFlightDate ($value){
		if (!Model\BaseModel::isDateValid($value, 'dM')){
			throw new \WSSDK\Model\ModelValidationException(get_class($this) . ": Date $value must match format DDMMM", 1);
		}
		$this->fields->ad_flight_date = $value;
	}
	public function setConfirmationInformation ($value){
		$this->fields->ad_confirmation_information = $value;
	}
	public function addCouponLeg(AirlineDataUATPLeg $value){
		$this->fields->ad_coupon_count ++;
		$value->setLegNumber($this->fields->ad_coupon_count);
		$this->legs[] = $value;
	}
	public function addTaxFee (AirlineDataUATPTaxFee $value){
		$this->fields->ad_tax_count ++;
		$value->setTaxFeeNumber($this->fields->ad_tax_count);
		$this->ees[] = $value;
	}

	/* GETTERS */
	public function getAgentNumericCode (){
		return $this->fields->ad_agent_numeric_code;
	}
	public function getAgentCountryCode (){
		return $this->fields->ad_agent_country_code;
	}
	public function getTicketDocumentNumber (){
		return $this->fields->ad_ticket_document_number;
	}
	public function getIsTicketElectronic(){
		return $this->fields->ad_ticket_electronic;
	}
	public function getTicketDateOfIssue (){
		return $this->fields->ad_ticket_date_of_issue;
	}
	public function getPassengerName (){
		return $this->fields->ad_passenger_name;
	}
	public function getCustomerFileReference (){
		return $this->fields->ad_customer_file_reference;
	}
	public function getFlightDate (){
		return $this->fields->ad_flight_date;
	}
	public function getCouponCount(){
		return $this->fields->ad_coupon_count;
	}
	public function getConfirmationInformation (){
		return $this->fields->ad_confirmation_information;
	}
	public function getTaxCount (){
		return $this->fields->ad_tax_count;
	}

}