<?php

use Fuel\Core\Uri;

class Helpers_View_Pagination
{
    /** @var array */
    private $sortable_fields;
    /** @var array */
    private $query_params;
    /** @var string */
    private $base_uri;
    /** @var int */
    private $all_results_count;
    /** @var int */
    private $page;
    /** @var int */
    private $per_page;

    public function __construct(
        string $base_uri,
        array $sortable_fields,
        array $query_params,
        int $all_results_count,
        int $per_page = 10
    ) {
        $this->base_uri = $base_uri;
        $this->sortable_fields = $sortable_fields;
        $this->query_params = $query_params;
        $this->all_results_count = $all_results_count;
        $this->per_page = $per_page;
        $this->page = isset($query_params['show_page']) ? (int)$query_params['show_page'] : 1;
    }

    public function toggle_field_order(string $sort_field, string $default_order = 'asc'): string
    {
        $this->verify_exists($sort_field);
        $sort_order = isset($this->query_params['sort_order']) ? $this->query_params['sort_order'] : $default_order;
        $nonFiltersApplied = 'draw_date' === $sort_field && !isset($this->query_params['sort_order']);
        $sort_order = $nonFiltersApplied ? 'desc' : $sort_order;
        $reversed_sort_order = $sort_order === 'asc' ? 'desc' : 'asc';
        return Uri::update_query_string(array_merge($this->query_params, ['sort' => $sort_field, 'sort_order' => $reversed_sort_order]), $this->base_uri);
    }

    private function verify_exists(string $field_name): void
    {
        if (!in_array($field_name, $this->sortable_fields)) {
            throw new InvalidArgumentException(sprintf('Given <%s> field does not exist. Allowed ones: <%s>', $field_name, implode(', ', $this->sortable_fields)));
        }
    }

    public function change_page(int $page): string
    {
        return Uri::update_query_string(array_merge($this->query_params, ['show_page' => $page]), $this->base_uri);
    }

    public function get_offset(): int
    {
        if ((int)$this->page === 1) {
            return 0;
        }
        return (int)$this->page * (int)$this->per_page - (int)$this->per_page;
    }

    public function get_per_page(): int
    {
        return (int)$this->per_page;
    }

    public function get_all_results_count(): int
    {
        return (int)$this->all_results_count;
    }

    public function get_current_page(): int
    {
        return (int)$this->page;
    }
}
