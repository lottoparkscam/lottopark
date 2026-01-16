<?php

namespace Tests\Unit\Classes\Helpers;

use Path;
use Test_Unit;

class PathTest extends Test_Unit
{
    /** @test */
    public function unify_path__from_windows()
    {
        $examplePath = 'this\is\example\path\\';
        $unifiedPath = Path::unifyPath($examplePath);
        $this->assertEquals('this' . DS . 'is' . DS . 'example' . DS . 'path' . DS, $unifiedPath);
    }

    /** @test */
    public function unify_path__from_linux()
    {
        $examplePath = 'this/is/example/path/';
        $unifiedPath = Path::unifyPath($examplePath);
        $this->assertEquals('this' . DS . 'is' . DS . 'example' . DS . 'path' . DS, $unifiedPath);
    }
}
