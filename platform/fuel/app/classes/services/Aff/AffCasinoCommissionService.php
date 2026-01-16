<?php

namespace Services;

use Fuel\Core\Input;
use Fuel\Core\Pagination;
use Repositories\WhitelabelAffSlotCommissionRepository;
use Container;
use Repositories\Aff\WhitelabelAffRepository;

class AffCasinoCommissionService
{
    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;
    private WhitelabelAffRepository $whitelabelAffRepository;
    
    public function __construct()
    {
        $this->whitelabelAffSlotCommissionRepository = Container::get(WhitelabelAffSlotCommissionRepository::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
    }

	private function isShowAllOptionSelected(?string $value): bool
	{
		return $value && $value === 'a';
	}

    public function prepareCasinoCommissionFilters(): array
    {
        $filterFields = $this->getFilterFields();
        $filters = array_map([$this, 'createFilter'], $filterFields);

        return array_filter($filters);
    }

    private function getFilterFields(): array
    {
        return [
            ['country', 'whitelabel_user.country'],
            ['id', 'whitelabel_user.token'],
            ['email', 'whitelabel_user.email'],
            ['login', 'whitelabel_aff.login'],
            ['name', 'whitelabel_user.name'],
            ['surname', 'whitelabel_user.surname'],
        ];
    }

    private function createFilter(array $field): ?array
    {
        $inputValue = Input::get('filter.' . $field[0]);
        if (!empty($inputValue) && !$this->isShowAllOptionSelected($inputValue)) {
            return $this->buildFilter($field, $inputValue);
        }

        return null;
    }

    private function buildFilter(array $field, string $inputValue): array
    {
        if ($field[0] === 'login') { // searching by the full phrase in the case of a login
            return [$field[1], $inputValue];
        }

        return [$field[1], '%' . $inputValue . '%'];
    }

	private function prepareCasinoCommissionsPagination(
        array $filters,
        int $whitelabelId,
        ?string $dateStart = null,
        ?string $dateEnd = null
        ): object
    {
        $commissionCount = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissionCountByReport(
            $filters,
            null,
            $whitelabelId,
            $dateStart,
            $dateEnd
        );
        
        $config = [
            'pagination_url' => '/affs/casinoCommissions?' . http_build_query(Input::get()),
            'total_items' => $commissionCount,
            'per_page' => 25,
            'uri_segment' => 'page'
        ];

        return Pagination::forge('affspagination', $config);
    }

	public function prepareAffCasinoCommissionsData(int $whitelabelId, array $dates): array
	{
		$filters = $this->prepareCasinoCommissionFilters();
		$pagination = $this->prepareCasinoCommissionsPagination(
			$filters, 
            $whitelabelId,
            $dates['date_start'] ?? null,
            $dates['date_end'] ?? null,
		);

		return [
            $filters,
			$pagination,
		];
	}
}
