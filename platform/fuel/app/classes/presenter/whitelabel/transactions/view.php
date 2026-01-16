<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 19.04.2019
 * Time: 12:07
 */

use Fuel\Core\Security;

/**
 * Presenter for views/whitelabel/transactions/view.php
 */
class Presenter_Whitelabel_Transactions_View extends Presenter_Presenter
{

    /**
     *
     */
    public function view() // 19.04.2019 12:27 Vordis TODO: wrap in base class and require implementation
    {
//        $this->set_safe('print_details', $this->closure_print_details_array());
    }

    /**
     * Print human readable information from array.
     * @param bool $recursive true if arrays= should be printed recursively.
     * @return Closure
     */
    protected function closure_print_details_array(bool $recursive = true): Closure // 19.04.2019 12:52 Vordis TODO: should go to base presenter, after some adjustments (broader scope of usage)
    {
        return $printClosure = function (array $array) use ($recursive, &$printClosure) {
            foreach ($array as $key => $item) { // go over every item in array
                // if item is an array call print recursively and don't print it's key // 19.04.2019 12:40 Vordis TODO: maybe print key also, but it would need some formatting
                if (is_array($item)) {
                    if ($recursive) {
                        $printClosure($item);
                    }
                    continue; // jump to end of iteration
                }

                // prepare key name: change _ to spaces and make first letter capital
                $key_display = str_replace('_', ' ', ucfirst($key));
                $key_display = str_replace('-', ' ', $key_display); // '-' to spaces
                $key_display = preg_replace("/(?<=\\w)(?=[A-Z])/"," $1", $key_display); // camel to spaced words // 19.04.2019 14:01 Vordis TODO: export somewhere, it might be useful as global helper 

                echo
                    '<span class="details-label" title="' . Security::htmlentities($key_display) . '">'
                    . Security::htmlentities($key_display) .
                    '</span>'
                    . $this->prepare_details_item_html($item); // 19.04.2019 12:44 Vordis TODO: on assumption item is string, if we want broader scope of uses it should be var_exported for non strings
            }
        };
    }

    /**
     * @param string|null $item
     * @return string
     */
    private function prepare_details_item_html(?string $item): string
    {
        // prepare value, return if tag is not default (span)
        if ($item === null) {
            $item = '-';
        } else if (substr($item, 0, 5) === '<?xml') {
            $dom = new DOMDocument;
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($item);
            $dom->formatOutput = true;
            // return in different tag
            return
                '<pre lang="xml" >'
                . Security::htmlentities($dom->saveXML()) .
                '</pre>';

        }

        // wrap item in html
        return
            '<span class="details-value">'
            . Security::htmlentities($item) .
            '</span>
                <br>';
    }

}