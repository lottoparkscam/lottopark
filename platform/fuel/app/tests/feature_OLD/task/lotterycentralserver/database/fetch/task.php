<?php


namespace Tests;

use Task_Result;

require_once(APPPATH . "/tests/feature/task/lotterycentralserver/task.php");

class Test_Feature_Classes_Task_Lotterycentralserver_Fetch_Task extends Test_Feature_Classes_Task_Lotterycentralserver_Task
{
    protected $response_stub;

    protected $response_class_mockable;

    protected $response_methods = ['__toString', '__get', 'define_additional_fields', 'is_valid', 'is_structure_invalid', 'define_validation_rules'];

    protected function prepare_mocked_response(array $response_attributes, int $status): void
    {
        $this->response_stub = $this->getMockBuilder($this->response_class_mockable)
            ->setConstructorArgs([$response_attributes, json_encode($response_attributes)])
            ->setMethodsExcept($this->response_methods)
            ->getMock();

        $this->response_stub->method('get_status_code')
            ->willReturn($status);

        // TODO: Mock fieldset validation
        //$this->response_stub->method('get_fieldset_validation')
        //    ->willReturn();

        $this->task_stub->method('fetch')
            ->willReturn($this->response_stub);
    }

    public function run_task_with_data_from_response(array $response_attributes, int $status = 200): void
    {
        if ($this->lottery === null){
            $this->create_lottery();
        }
        $this->prepare_mocked_response($response_attributes, $status);

        // TODO: Mock fieldset validation first, then assert
        //self::assertTrue($this->response_stub->is_valid());

        $this->task_stub->run();
    }
}