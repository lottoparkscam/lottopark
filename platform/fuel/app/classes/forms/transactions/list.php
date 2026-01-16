<?php

/**
 *
 */
class Forms_Transactions_List
{
    
    /**
     *
     * @var type
     */
    private $whitelabel = [];
    
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     */
    public function __construct($source, $whitelabel = [])
    {
        if (!empty($source) && $source == Helpers_General::SOURCE_ADMIN) {
            if (Input::get("filter.whitelabel") != null &&
                Input::get("filter.whitelabel") != "a"
            ) {
                $whitelabel = [];
                $whitelabel['id'] = intval(Input::get("filter.whitelabel"));
            }
        }
        
        $this->source = $source;
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel():? array
    {
        return $this->whitelabel;
    }
    
    /**
     *
     * @return int
     */
    public function get_source(): int
    {
        return $this->source;
    }

    /**
     *
     */
    public function get_prepared_form()
    {
    }
    
    /**
     *
     * @param View $inside
     * @param string $rparam
     * @return null
     */
    public function process_form(&$inside, $rparam)
    {
        // This value depends on source given
        // This could be null because it could be called from ADMIN
        $whitelabel = $this->get_whitelabel();
        $source = $this->get_source();
        
        $cache_list_id = 0;
        if (!empty($source) && (int)$source === Helpers_General::SOURCE_ADMIN) {
            $cache_list_id = 1;
        }
        
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages(
            $whitelabel,
            $cache_list_id
        );
        $whitelabel_languages_indexed_by_id = Lotto_Helper::prepare_languages($whitelabel_languages);
        $inside->set("langs", $whitelabel_languages_indexed_by_id);
        $inside->set("type", $rparam);

        return ;
    }
}
