<?php

use Fuel\Core\Validation;

/**
 * Description of Forms_Whitelabel_Payment_ShowData
 */
interface Forms_Whitelabel_Payment_ShowData
{
    /**
     * I have done that because I cant easily do that method by use Presenter separately:(
     * Maybe in the future
     *
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array;
    
    /**
     *
     * @param Validation|null $additional_values_validation
     * @return array
     */
    public function get_data(
        ?Validation $additional_values_validation
    ): array;
}
