<?php

namespace Tests;

use Fuel\Core\Database_Query_Builder;
use Fuel\Core\DB;
use Task_Result;

require_once(APPPATH . "/tests/feature/task/lotterycentralserver/task.php");

class Test_Feature_Classes_Task_Lotterycentralserver_Database_Task extends Test_Feature_Classes_Task_Lotterycentralserver_Task
{
    protected $previous_task_result;

    protected $query_builder_mock;

    protected $query_builder_mock_class = 'Database_Query_Builder_Mockable';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Task_Lotterycentralserver_Database_Task
     */
    protected $task_stub;

    public function setUp(): void
    {
        parent::setUp();
        $this->previous_task_result = new Task_Result();
        $this->task_stub->method('get_previous_task_result')->willReturn($this->previous_task_result);
        $this->assertTrue(class_exists($this->query_builder_mock_class));
        $this->query_builder_mock = $this->createMock($this->query_builder_mock_class);
        $this->query_builder_mock->method('execute')->willReturn([0]);
    }

}
