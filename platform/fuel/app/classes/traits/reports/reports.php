<?php

/**
 *
 */
trait Traits_Reports_Reports
{
    /**
     *
     * @var string
     */
    protected $country = null;

    /**
     *
     * @var string
     */
    protected $language = null;
    
    /**
     *
     * @var array
     */
    protected $countries;
    
    /**
     *
     * @var array
     */
    protected $languages;
    
    /**
     *
     * @var string
     */
    protected $date_format = "m/d/Y";
    
    /**
     *
     * @var null|\DateTime
     */
    protected $date_start_value = null;

    /**
     *
     * @var null|\DateTime
     */
    protected $date_end_value = null;
    
    /**
     *
     * @var string
     */
    protected $filter_add = "";

    /**
     *
     * @var array
     */
    protected $params = [];
    
    /**
     *
     * @return void
     */
    public function prepare_countries(): void
    {
        if (empty($this->countries)) {
            $this->countries = Lotto_Helper::get_localized_country_list();
            $this->prepare_inside();
            $this->inside->set("countries", $this->countries);
        }
    }
    
    /**
     *
     * @return array|null
     */
    public function get_countries():? array
    {
        if (empty($this->countries)) {
            $this->prepare_countries();
        }
        return $this->countries;
    }
    
    /**
     *
     * @return void
     */
    public function prepare_languages(): void
    {
        if (empty($this->languages)) {
            $this->languages = Model_Language::get_all_languages();
            $this->prepare_inside();
            $this->inside->set("languages", $this->languages);
        }
    }
    
    /**
     *
     * @return array|null
     */
    public function get_languages():? array
    {
        if (empty($this->languages)) {
            $this->prepare_languages();
        }
        return $this->languages;
    }

    /**
     *
     * @return string
     */
    public function get_filter_add(): string
    {
        return $this->filter_add;
    }

    /**
     *
     * @return array
     */
    public function get_params(): array
    {
        return $this->params;
    }
    
    /**
     *
     * @param string $date_start_time
     * @return string
     */
    public function prepare_and_get_date_start_value(string $date_start_time): string
    {
        if (empty($this->date_start_value)) {
            $date_start_value = DateTime::createFromFormat(
                $this->date_format,
                $date_start_time,
                new DateTimeZone("UTC")
            );
            $date_start_value->setTime(0, 0, 0);
            
            $this->date_start_value = $date_start_value->format("Y-m-d H:i:s");
        }
        
        return $this->date_start_value;
    }
    
    /**
     *
     * @param string $date_end_time
     * @return string
     */
    public function prepare_and_get_date_end_value(string $date_end_time): string
    {
        if (empty($this->date_end_value)) {
            $date_end_value = DateTime::createFromFormat(
                $this->date_format,
                $date_end_time,
                new DateTimeZone("UTC")
            );

            if ($date_end_value === false) {
                $date_end_value = new DateTime("now", new DateTimeZone("UTC"));
            }

            $date_end_value->setTime(23, 59, 59);
            
            $this->date_end_value = $date_end_value->format("Y-m-d H:i:s");
        }
        
        return $this->date_end_value;
    }
    
    /**
     * Prepare start and end dates based on rages dates from Front-end
     * to make possible easily use in other code
     *
     * @param string $date_start_time
     * @param string $date_end_time
     * @return void
     */
    public function prepare_dates(
        string $date_start_time,
        string $date_end_time
    ): void {
        $this->prepare_and_get_date_start_value($date_start_time);
        $this->prepare_and_get_date_end_value($date_end_time);
        $this->prepare_inside();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("date_start", $this->date_start_value);
            $this->inside->set("date_end", $this->date_end_value);
        }
    }

    /**
     *
     * @param string $column_name
     * @return array
     */
    public function prepare_dates_based_on_column_name(
        string $column_name
    ): array {
        $filter_dates = [];
        $params_dates = [];

        $filter_dates[] = " AND " . $column_name . " >= :date_start ";
        $params_dates[] = [":date_start", $this->date_start_value];

        $filter_dates[] = " AND " . $column_name . " <= :date_end ";
        $params_dates[] = [":date_end", $this->date_end_value];

        $filter_dates_whole = implode("", $filter_dates);

        return [
            $filter_dates_whole,
            $params_dates
        ];
    }

    /**
     *
     * @param string $language
     * @param string $country
     * @return void
     */
    public function prepare_filters(
        string $language = null,
        string $country = null
    ): void {
        $filter_add_prepare = [];

        $this->prepare_languages();
        if ($language !== null &&
            isset($this->languages[$language])
        ) {
            $this->language = $language;
        }

        $this->prepare_countries();
        if ($country !== null &&
            isset($this->countries[$country])
        ) {
            $this->country = $country;
        }

        if ($this->language !== null) {
            $filter_add_prepare[] = " AND whitelabel_user.language_id = :language ";
            $this->params[] = [":language", $this->language];
        }

        if ($this->country !== null) {
            $filter_add_prepare[] = " AND whitelabel_user.country = :country ";
            $this->params[] = [":country", $this->country];
        }

        $this->filter_add = implode("", $filter_add_prepare);
    }
    
    /**
     *
     * @return string
     */
    public function get_date_start_value(): string
    {
        return $this->date_start_value;
    }
    
    /**
     *
     * @return string
     */
    public function get_date_end_value(): string
    {
        return $this->date_end_value;
    }
    
    /**
     *
     * @return null|string
     */
    public function get_language():? string
    {
        return $this->language;
    }
    
    /**
     *
     * @return null|string
     */
    public function get_country():? string
    {
        return $this->country;
    }
}
