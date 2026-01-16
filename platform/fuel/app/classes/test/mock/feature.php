<?php

use Helpers\ArrayHelper;

abstract class Test_Mock_Feature extends \Test_Feature
{
    protected $mockable_classes = [];
    protected $mockable_suffix = "_Mockable";

    public function setUp(): void
    {
        parent::setUp();
        $this->load_mockable_classes();
    }

    protected function load_mockable_classes()
    {
        foreach ($this->mockable_classes as $classname){
            Test_Mock_Loader::load_class_as_mockable($classname, ArrayHelper::last(explode('\\',$classname)) .
                $this->mockable_suffix);
        }
    }
}