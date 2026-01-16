<?php

use Firebase\JWT\JWT;
use Models\AdminWhitelabel;

class Controller_Authentication extends Controller_Rest
{
    /**
     *
     * @var array
     */
    private $algorithm = ['HS256'];

    public function before()
    {
        parent::before();
        if (!Lotto_Helper::allow_access("admin")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }
    }
    /**
     *
     * @access  private
     * @return array
     */
    private function auth_error(): array
    {
        return [
              'code' => 400,
              'message' => _('Wrong credentials! Please try again.')
            ];
    }

    private function user_with_empty_permissions(): array
    {
        return [
            'code' => 400,
            'message' => _('Contact admin to assign any permissions.')
        ];
    }

    /**
     *
     * @access  public
     * @return array
     */
    public function post_authenticate(): array
    {
        Config::load("admin", true);
        $key = Config::get("admin.rsa_private_key");

        $login_name = \Input::json('username');
        $login_password = \Input::json('password');
        $user = Model_Admin_User::login_user(
            $login_name,
            $login_password
        );

        if ($user == null) {
            return $this->auth_error();
        }

        /** @var array $admin_whitelabels */
        $admin_whitelabels = AdminWhitelabel::find_by('admin_user_id', $user['id']);

        if (empty($admin_whitelabels)) {
            return $this->user_with_empty_permissions();
        }

        $token = [
            "message" => 'logged',
            "user" => $user['id']
        ];
        $jwt = JWT::encode($token, $key, $this->algorithm[0]);

        return [
            'code' => 200,
            'token' => $jwt,
            'user' => $user
        ];
    }
}
