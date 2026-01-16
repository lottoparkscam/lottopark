<?php


class Tests_E2e_Controller_Api_Auth_Register extends Test_E2e_Controller_Api
{
    private string $email;

    private string $method;

    private string $endpoint;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel->set([
            'use_logins_for_users' => false
        ]);
        $this->whitelabel->save();

        $this->email = 'username1@lottopark.loc';
        $this->method = 'POST';
        $this->endpoint = '/api/auth/register';
    }

    public function test_post_index_create()
    {
        $body = [
            'email' => $this->email,
            'password' => '12345678',
            'language' => 'en_GB',
            'currency' => 'EUR'
        ];

        $options = [
            'form_params' => $body
        ];

        $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertIsArray($new_user);

        $new_user[0]->delete();
    }

    public function test_post_index_password_validation()
    {
        // check if cannot create user without password
        $body = [
            'email' => $this->email,
            'language' => 'en_GB',
            'currency' => 'EUR'
        ];

        $options = [
            'form_params' => $body
        ];

        $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertNull($new_user);



        // check password shorter than 6

        $body['password'] = 'test';

        $options = [
            'form_params' => $body
        ];

        $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertNull($new_user);



        // check password equal 6

        $body['password'] = '123456';

        $options = [
            'form_params' => $body
        ];

        $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertIsArray($new_user);

        $new_user[0]->delete();
    }

    public function test_post_index_email_validation()
    {
        // check if cannot create user without email
        $body = [
            'password' => '12345678',
            'language' => 'en_GB',
            'currency' => 'EUR'
        ];

        $options = [
            'form_params' => $body
        ];

        $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertNull($new_user);

        // check if cannot create user with invalid email
        $body['email'] = 'to_napewno_nie-jest_email';

        $options = [
            'form_params' => $body
        ];

        $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertNull($new_user);
    }

    public function test_post_index_language_check()
    {
        // check if language is not set then its en_GB
        $body = [
            'email' => $this->email,
            'password' => '12345678',
            'currency' => 'EUR'
        ];

        $options = [
            'form_params' => $body
        ];

        $response = $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        /** @var Model_Whitelabel_User $new_user */
        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertIsArray($new_user);
        $this->assertEquals(1, $new_user[0]['language_id']);

        $new_user[0]->delete();
    }

    public function test_post_index_currency_validation()
    {
        // check if cannot create user without currency

        $body = [
            'email' => $this->email,
            'password' => '12345678',
        ];

        $options = [
            'form_params' => $body
        ];

        $this->get_response_with_security_check($this->method, $this->endpoint, $options);

        $new_user = Model_Whitelabel_User::find([
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertNull($new_user);
    }

    // TODO:
    // check additional fields
}