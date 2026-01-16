<?php
namespace WSSDK;
require_once __DIR__."/StatusResponse.php";

/**
 * Provide a container and basic functionality for xml responses from the WS API
 */
class BaseResponse {

	protected $body, $xml, $headers;
	public function __construct($body, $headers)
	{
		$this->body=XML::XMLToObject($body);
		$this->xml=$body;
		$this->headers=$headers;
	}

	public function getBody(){
		return $this->body;
	}
	public function getHeaders(){
		return $this->headers;
	}
	public function getXML(){
		return $this->xml;
	}
	public function hasError() {
		return isset($this->body->errors);
	}
	public function getError() {
		if ($this->hasError()){
			return $this->getBody();
		} else {
			return null;
		}
	}

}

