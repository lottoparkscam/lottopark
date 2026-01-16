<?php

/**
* Test Mock Mocker.
* @author Marcin Klimek <marcin.klimek at gg.international>
* Date: 2019-09-20
* Time: 12:57:31
*/
abstract class Test_Mock_Mocker
{

    /**
     * Provide create logic.
     *
     * @param array $args arguments for mocking function.
     * @return array
     */
    abstract protected function create(...$args): array;

    /**
     * Mock data for object.
     *
     * @param array $args arguments for mocking function.
     * @return array mocked data.
     */
    public static function mock(...$args): array
    {
        return (new static)->create(...$args);
    }
}