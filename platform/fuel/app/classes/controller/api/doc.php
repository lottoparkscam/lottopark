<?php

use Fuel\Core\Controller;
use OpenApi\Generator;
use Services\Api\Balance\DocService;
use Wrappers\Decorators\ConfigContract;

class Controller_Api_Doc extends Controller
{
    private ConfigContract $config;
    /**
     * @var DocService
     */
    private DocService $docService;

    public function __construct(\Request $request)
    {
        parent::__construct($request);
        $this->config = Container::get(ConfigContract::class);
        $this->docService = Container::get(DocService::class);
    }

    public function get_index(): string
    {
        $this->docService->grant_cors_access();
        return $this->docService->get_doc_in_json() ?? Generator::scan([APPPATH . '/classes/controller/api/'])->toJson();
    }
}
