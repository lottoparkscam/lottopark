<?php

namespace Tests\Unit\Helpers;

use Helpers\ArrayHelper;
use Test_Unit;

final class ArrayHelperTest extends Test_Unit
{
    /** @test */
    public function deleteValuesLikeForAssocArrayWithoutSpecifiedColumnShouldRemoveValue(): void
    {
        $deleteValuesLike = ['test'];

        $array = [
            'abc' => 'abc',
            'test' => 'test absxiuasbdiasd'
        ];

        $expected = [
            'abc' => 'abc'
        ];

        $this->assertSame($expected, ArrayHelper::deleteValuesLikeForAssocArray($array, $deleteValuesLike));
    }

    /** @test */
    public function deleteValuesLikeForAssocArrayWithSpecifiedColumnShouldRemoveValue(): void
    {
        $deleteValuesLike = ['test'];

        $array = [
            'abc' => 'abc',
            'test' => 'test absxiuasbdiasd',
            'removeOnlyFromThisColumn' => 'testcc'
        ];

        $expected = [
            'abc' => 'abc',
            'test' => 'test absxiuasbdiasd',
        ];

        $this->assertSame($expected, ArrayHelper::deleteValuesLikeForAssocArray($array, $deleteValuesLike, 'removeOnlyFromThisColumn'));
    }

    /** @test */
    public function deleteValuesLikeForAssocArrayDoesNotHaveSpecifiedColumnWillReturnSame(): void
    {
        $deleteValuesLike = ['test'];

        $expected = [
            'abc' => 'abc',
            'test' => 'test absxiuasbdiasd',
            'dsad' => 'sadasx'
        ];

        $this->assertSame($expected, ArrayHelper::deleteValuesLikeForAssocArray($expected, $deleteValuesLike, 'columnWhichDoesNotExist'));
    }

    /** @test */
    public function deleteValuesForMultiDimensionalArrayHasMultiValuesShouldRemoveAssocArrayFromMulti(): void
    {
        $valuesToRemove = ['test', 'second'];
        $columnToCheck = 'file';

        $array = $this->getMultiArray();

        $expected = [
            [
                'class' => 'aaa',
                'file' => 'ccc'
            ],
        ];

        $this->assertSame($expected, ArrayHelper::deleteValuesForMultiDimensionalArray($array, $valuesToRemove, true, true, $columnToCheck));
    }

    /** @test */
    public function deleteValuesForMultiDimensionalArrayHasMultiValuesShouldNotRemoveAssocArrayFromMulti(): void
    {
        $valuesToRemove = ['test', 'second'];
        $columnToCheck = 'file';

        $array = $this->getMultiArray();

        $expected = [
            [
                'class' => 'test',
            ],
            [
                'class' => 'test',
            ],
            [
                'class' => 'aaa',
                'file' => 'ccc'
            ],
        ];

        $this->assertSame($expected, ArrayHelper::deleteValuesForMultiDimensionalArray($array, $valuesToRemove, true, false, $columnToCheck));
    }

    public function getMultiArray(): array
    {
        return [
            [
                'class' => 'test',
                'file' => 'test',
            ],
            [
                'class' => 'test',
                'file' => 'second',
            ],
            [
                'class' => 'aaa',
                'file' => 'ccc'
            ],
        ];
    }

    /** @test */
    public function arraySpliceWithoutKeysShouldHaveValidIndexes(): void
    {
        $array = [
            'asd',
            'bcd',
            'efg',
            'ijk'
        ];

        $expected = [
            2 => 'efg',
            3 => 'ijk'
        ];

        $this->assertSame($expected, ArrayHelper::arraySpliceWithoutKeys($array, 2));
    }

    /** @test */
    public function arraySpliceWithoutKeysDoesNotHaveSpecifiedIndexShouldReturnEmptyArray(): void
    {
        $array = [
            'asd',
            'bcd',
            'efg'
        ];

        $expected = [];

        $this->assertSame($expected, ArrayHelper::arraySpliceWithoutKeys($array, 4));
    }
}
