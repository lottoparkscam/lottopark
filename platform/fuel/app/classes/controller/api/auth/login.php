<?php

use Fuel\Core\Response;
use Fuel\Core\Validation;
use Services\Api\Controller;
use Services\Api\Reply;

class Controller_Api_Auth_Login extends Controller
{
    /**
     * @OA\Info(
     *     title="Whitelotto API",
     *     version="1.0",
     *     description="Use all endpoints with suffix .json or .xml at the end of url."
     * )
     *
     * @OA\Server(url="https://api.lottopark.com/api", description="Where lottopark.com is your domain with 'api' prefix.")
     *
     * @OA\SecuritySchemes(
     *     @OA\SecurityScheme(
     *          securityScheme="Key",
     *          type="apiKey",
     *          name="x-whitelotto-key",
     *          in="header",
     *          description="The key that you received",
     *     ),
     *     @OA\SecurityScheme(
     *          securityScheme="Nonce",
     *          type="apiKey",
     *          name="x-whitelotto-nonce",
     *          in="header",
     *          description="Unique number has not been used before. For example: 1610968047567 (microtime * 1000)"
     *     ),
     *     @OA\SecurityScheme(
     *          securityScheme="Signature",
     *          type="apiKey",
     *          name="x-whitelotto-signature",
     *          in="header",
     *          description="Created like example:
                    hash_hmac(
                        'sha512',
                        $uri . $nonce . hash(
                            'sha256',
                            json_encode($getUrlParams)
                        ),
                        $apiSecret
                    ); where
                    $uri=/api/balance/add,
                    $nonce=1610968047567,
                    $getUrlParams=['param1' => 'a', 'param2' => 'b'],
                    $secret=Received from us"
     * ))
     *
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"login", "password"},
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     description="Use login only if your users identify by login. In other case use email but put it in login property in form body.",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="remember_me",
     *                     type="bool",
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="",
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="status",
     *                          type="string",
     *                          example="success"
     *                      ),
     *                      @OA\Property(
     *                          property="data",
     *                          type="string",
     *                          example={"autologin_url":"https://some.url"}
     *                      )
     *                  )
     *              )
     *          )
     * )
     */
    public function post_index(): Response
    {
        $validation = Validation::forge('controller_api_auth_login');

        $validation->add("email", "Email address")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('required')
            ->add_rule('valid_email');

        $validation->add("password", "Password")
            ->add_rule("trim")
            ->add_rule('stripslashes')
            ->add_rule("required");

        $validation->add("remember_me", "Remember Me")
            ->add_rule("trim")
            ->add_rule("stripslashes")
            ->add_rule("match_collection", ['true', 'false'], true);

        $is_valid = $validation->run();

        if (!$is_valid) {
            $errors = Lotto_Helper::generate_errors($validation->error());

            $response = $this->returnResponse(
                $errors,
                Reply::BAD_REQUEST
            );

            return $response;
        }

        $whitelabel_user = Model_Whitelabel_User::find_one_by([
            'whitelabel_id' => $this->whitelabel->id,
            'email' => $validation->validated('email'),
            'is_deleted' => 0,
            'is_active' => 1
        ]);

        if (!$whitelabel_user) {
            $response = $this->returnResponse(
                ["Wrong credentials"],
                Reply::BAD_REQUEST
            );

            return $response;
        }

        $salt = $whitelabel_user['salt'];

        $hash_password = Lotto_Security::generate_hash(
            $validation->validated('password'),
            $salt
        );

        if ($hash_password !== $whitelabel_user['hash']) {
            $response = $this->returnResponse(
                ["Wrong credentials"],
                Reply::BAD_REQUEST
            );

            return $response;
        }

        $domain = $this->whitelabel->domain;

        $login_date = new DateTime("now", new DateTimeZone("UTC"));
        $login_hash = Lotto_Security::generate_time_hash($salt, $login_date);

        $whitelabel_user->set([
            'login_hash_created_at' => $login_date->add(new DateInterval("P1D"))->format("Y-m-d H:i:s"),
            'login_hash' => $login_hash,
            'login_by_hash_last' => null
        ]);
        $whitelabel_user->save();

        $remember_me_field_exists = !empty($remember_me_string = $validation->validated('remember_me'));
        $remember_me = $remember_me_field_exists && $remember_me_string === 'true' ? 'remember' : '';

        $response_data = [
            'autologin_url' => 'https://' . $domain . '/autologin/' . $login_hash . '/' . $remember_me
        ];

        return $this->returnResponse($response_data);
    }
}
