<?php

/**
 * @deprecated
 * Description of Forms_Aff_Withdrawal_Method
 */
class Forms_Aff_Withdrawal_Method extends Forms_Main
{
    /**
     *
     * @var string
     */
    protected $fieldset_name = "";

    /**
     *
     * @param string $fieldset_name
     */
    public function __construct(string $fieldset_name)
    {
        $this->fieldset_name = $fieldset_name;
    }
    
    /**
     *
     * @return array
     */
    public function get_fields(): array
    {
        $fields = [];
        
        return $fields;
    }
    
    /**
     *
     * @param array $whitelabel
     * @return array
     */
    public static function get_methods_list_by_whitelabel(array $whitelabel): array
    {
        $methods = [];
        
        $withdrawal_methods = Model_Whitelabel_Withdrawal::get_whitelabel_withdrawals($whitelabel);
        
        foreach ($withdrawal_methods as $withdrawal_method) {
            $withdrawal_method_id = (int)$withdrawal_method['id'];
            $methods[$withdrawal_method_id] = $withdrawal_method['name'];
        }
        
        return $methods;
    }
}
