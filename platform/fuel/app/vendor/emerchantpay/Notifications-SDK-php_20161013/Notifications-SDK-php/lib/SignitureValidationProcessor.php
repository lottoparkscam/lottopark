<?php

namespace NotificationSDK;

/**
 * Error passing the signature against the params
 */
class SignitureValidationProcessorException extends \Exception {}

/**
 * iProcessor implementation for parameter signature verification.
 * This processor is registered automatically as a catch all handler, and is run first by any request handler.
 */
class SignitureValidationProcessor implements iProcessor {

    private $secret;

    public function __construct($secret){
        $this->secret = $secret;
    }

	/**
	 * Verify that no parms have been changed by matching them against the signature
	 * @param  Array  $paramArray 	param array from iNotification->getData()
	 * @param  boolean $secret     Salt to add to the signature
	 * @return boolean
	 */
	private function isParamSigValid ($paramArray)
    {
        $sentSignature = @$paramArray['PS_SIGNATURE'];
        unset($paramArray['PS_SIGNATURE']);
        $string = '';
        ksort($paramArray, SORT_STRING);
        foreach ($paramArray as $key => $value) {
            $string .= "&" . $key . '=' . $value;
        }
        switch (@$paramArray['PS_SIGTYPE']) {
            case 'MD5':
            case 'md5':
            case 'PSMD5':
                $signature = md5($this->secret . $string);
                break;
            case 'sha1':
            case 'SHA1':
            case 'PSSHA1':
                $signature = sha1($this->secret . $string);
                break;
            default:
                throw new SignitureValidationProcessorException("PS_SIGTYPE not supported", 1);
        }

        if ($sentSignature != $signature) {
            return false;
        }

        return true;
    }

	public function process (iNotification $notification, $DEBUG) {

        $n = $notification->getData();
		if($this->isParamSigValid($n) === false){
			throw new SignitureValidationProcessorException("PS_SIGNATURE not valid", 1);
		}

        if ($DEBUG){
            //fputs(STDOUT, "PROCESSOR: SignitureValidationProcessor for type \"" . $notification->getType() . "\" - " . $n['PS_SIGTYPE'] . "[" . $n['PS_SIGNATURE'] . "]\n");
        }

        // strip signature parameters from request
        unset($n['PS_SIGNATURE']);
        unset($n['PS_SIGTYPE']);
        unset($n['PS_EXPIRETIME']);
        $notification = Notification::FromKeyValueArray($n);

        return $notification;

	}
}