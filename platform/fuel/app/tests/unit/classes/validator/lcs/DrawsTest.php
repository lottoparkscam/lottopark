<?php

namespace Tests\Unit\Classes\Validator\Lcs;

use Validator_Lcs_Draws;
use InvalidArgumentException;

class DrawsTest extends \Test_Unit
{
    private $valid_lcs_draws_response = [];

    private Validator_Lcs_Draws $validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->valid_lcs_draws_response = json_decode(
            file_get_contents(
                APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, 'tests\data\lcs\draws_to_sync_response.json')
            ),
            true
        );
        $this->validator = $this->container->get(Validator_Lcs_Draws::class);
    }

    /** @test */
    public function validatesValidData_withNoErrors(): void
    {
        foreach ($this->valid_lcs_draws_response as $response) {
            $this->validator->validate($response);
        }
        $this->assertTrue(true);
    }

    /** @test */
    public function whenOneOfRequiredFieldsIsMissing__ThrowsError(): void
    {
        $required_fields = Validator_Lcs_Draws::DRAW_REQUIRED_FIELDS;
        $data = reset($this->valid_lcs_draws_response);
        $random_field_index = array_rand($required_fields);
        $random_field_key = $required_fields[$random_field_index];
        unset($data[$random_field_key]);
        $this->expectException(InvalidArgumentException::class);
        $this->validator->validate($data);
    }
}
