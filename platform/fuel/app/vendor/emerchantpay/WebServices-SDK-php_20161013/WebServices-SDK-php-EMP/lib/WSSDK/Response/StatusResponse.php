<?php
/**
  * @filesource
  */

namespace WSSDK\Request;
use \WSSDK\BaseResponse as BaseResponse;

/**
 * Extended response with functionality to report the status of a request
 */
class StatusResponse extends BaseResponse {

	private
		$approval = ['A','Approval'],
		$error = ['E','Error'],
		$declined = ['D','Declined'],
		$pending = ['P', 'Pending'],
		$wait = ['W','Wait'],
		$unknown = ['?','Unknown'];

	public function getStatus(){

		$result = $this->unknown;
		$response = $this->body->response;
		switch ($response) {
			case 'A': $result = $this->approval; break;
			case 'E': $result = $this->error; break;
			case 'D': $result = $this->declined; break;
			case 'P': $result = $this->pending; break;
			case 'W': $result = $this->wait; break;
		}

		return $result;

	}

	public function hasError() {
		return isset($this->body->errors) || $this->body->response === 'E';
	}

	public function getError() {
		if ($this->hasError()){
			if ($this->body->response !== 'E'){
				return $this->body->responsetext;
			} else {
				return $this->getBody();
			}
		} else {
			return null;
		}
	}

}


