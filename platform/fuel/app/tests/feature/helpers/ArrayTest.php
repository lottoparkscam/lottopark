<?php

namespace Unit\Helpers;

use Helpers\ArrayHelper;
use Test_Feature;

class ArrayTest extends Test_Feature
{
    /** @test */
    public function search_item_in_array__by_item_value__return_found_key()
    {
        $people = [
            33 => ['name' => 'Andrzej', 'age' => 1234],
            37 => ['name' => 'Adrian', 'age' => 6523],
            99 => ['name' => 'Adam', 'age' => 9999]
        ];

        $this->searchInArray($people, 'name', 'Andrzej', 33);
        $this->searchInArray($people, 'name', 'Adam', 99);
        $this->searchInArray($people, 'name', 'Adrian', 37);
    }

    /** @test */
    public function create_single_array__return_single_array()
    {
        $people = [
            33 => ['name' => 'Andrzej', 'age' => 1234],
            37 => ['name' => 'Adrian', 'age' => 6523],
            99 => ['name' => 'Adam', 'age' => 9999]
        ];

        $singleArray = ArrayHelper::createSingleArrayFromValue($people, 'name');
        $this->assertIsArray($singleArray);
        $this->assertCount(3, $people);
        $this->assertContains('Andrzej', $singleArray);
        $this->assertContains('Adrian', $singleArray);
        $this->assertContains('Adam', $singleArray);
        $this->assertSame($people[33]['name'], $singleArray[0]);
        $this->assertSame($people[37]['name'], $singleArray[1]);
        $this->assertSame($people[99]['name'], $singleArray[2]);
    }

    private function searchInArray(array $array, string $key, string $value, int $expectedKey): void
    {
        $foundKey = ArrayHelper::getKeyOfItemFromArrayByItemValue($array, $key, $value);
        $this->assertSame($expectedKey, $foundKey);
    }

    /** @test */
    public function removeArrayAndArraySubItemsByKeys__CorrectlyRemovedKeys(): void
    {
        $originalArray = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => [
                'subkey1' => 'subvalue1'
            ]
        ];

        $keysToRemove = ['key2', 'key3'];

        ArrayHelper::removeArrayAndArraySubItemsByKeys($originalArray, $keysToRemove);

        $this->assertArrayNotHasKey('key2', $originalArray);
        $this->assertArrayNotHasKey('key3', $originalArray);
        $this->assertArrayHasKey('key1', $originalArray);
    }

    /** @test */
    public function removeArrayAndArraySubItemsByKeys_RemovesKeysAnywhereInArray_KeysAndSubKeysAreRemoved(): void
    {
        $originalArray = [
            'key1' => 'value1',
            'key2' => [
                'unwantedSubKey1' => 'subValue1',
                'unwantedKey' => 'unwantedValue'
            ],
            'key3' => [
                'subKey2' => 'subValue2',
                'unwantedKey' => 'unwantedValue'
            ]
        ];

        $keysToRemove = ['unwantedKey', 'unwantedSubKey1'];

        ArrayHelper::removeArrayAndArraySubItemsByKeys($originalArray, $keysToRemove);

        $expectedArrayAfterRemoval = [
            'key1' => 'value1',
            'key2' => [],
            'key3' => [
                'subKey2' => 'subValue2',
            ]
        ];

        $this->assertEquals($expectedArrayAfterRemoval, $originalArray);
    }

    /** @test */
    public function removeArrayAndArraySubItemsByKeys_KeyToRemoveIsNotPresentInArray_DoesNotThrowError(): void
    {
        $originalArray = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => [
                'subkey1' => 'subvalue1'
            ]
        ];

        $expectedArray = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => [
                'subkey1' => 'subvalue1'
            ]
        ];

        $keysToRemove = ['keyNotDefinedInArray'];
        ArrayHelper::removeArrayAndArraySubItemsByKeys($originalArray, $keysToRemove);

        $this->assertEquals($expectedArray, $originalArray);
    }
}
