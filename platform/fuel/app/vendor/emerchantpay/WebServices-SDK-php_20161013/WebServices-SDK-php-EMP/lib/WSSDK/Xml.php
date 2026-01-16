<?php
namespace WSSDK;

/**
 * Provides helper functions for handeling the serialization && deserialization of XML
 */
class XML {

	/* =========== */
	/* XML HELPERS */
	/* =========== */

	public static function XMLToObject($xml) {
        return simplexml_load_string($xml, "SimpleXMLElement", LIBXML_PARSEHUGE);
	}


	public static function objectToXML(\stdClass $obj, $node_block='nodes', $node_name='node') {
        $arr = get_object_vars($obj);
        return self::ArrayToXML($arr, $node_block, $node_name);
    }


    public static function ArrayToXML($array, $node_block='nodes', $node_name='node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }


    private static function generateXmlFromArray($array, $node_name) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}