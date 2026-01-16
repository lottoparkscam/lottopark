<?php

declare(strict_types=1);

namespace Services\OAuth2Server;

use Container;
use Fuel\Core\Database_Result;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\RefreshTokenInterface;
use Wrappers\Db;

/**
 * WhiteLotto storage implementation to replace default OAuth2\Storage\Pdo
 * implementing minimum storage types for OpenID connection.
 *
 * @see https://bshaffer.github.io/oauth2-server-php-docs/storage/custom/
 */
class WhiteLottoStorage implements
    ClientCredentialsInterface,
    AuthorizationCodeInterface,
    OpenIDAuthorizationCodeInterface,
    AccessTokenInterface,
    RefreshTokenInterface
{
    protected Db $db;
    protected array $config;

    public function __construct($config = [])
    {
        $this->db = Container::get(Db::class);

        $this->config = array_merge([
            'client_table' => 'whitelabel_oauth_client',
            'access_token_table' => 'whitelabel_oauth_access_token',
            'refresh_token_table' => 'whitelabel_oauth_refresh_token',
            'code_table' => 'whitelabel_oauth_authorization_code',
        ], $config);
    }

    /**
     * @param string $client_id
     * @return array|mixed
     */
    public function getClientDetails($client_id)
    {
        /** @var Database_Result $queryResponse */
        $queryResponse = $this->db->select(
            'client_id',
            'client_secret',
            'redirect_uri',
            'grant_types',
            'scope'
        )
            ->from($this->config['client_table'])
            ->where('client_id', $client_id)
            ->execute();

        return $queryResponse->as_array()[0] ?? false;
    }

    /**
     * @param mixed $client_id
     * @return bool|null
     */
    public function getClientScope($client_id)
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }

    /**
     * @param $client_id
     * @param $grant_type
     * @return bool
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /**
     * @param string $client_id
     * @param null|string $client_secret
     * @return bool
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        /** @var Database_Result $queryResponse */
        $queryResponse = $this->db->select(
            'client_id',
            'client_secret',
            'redirect_uri',
            'grant_types',
            'scope'
        )
            ->from($this->config['client_table'])
            ->where('client_id', $client_id)
            ->execute();

        $result = $queryResponse->as_array()[0] ?? false;

        return $result && $result['client_secret'] == $client_secret;
    }

    /**
     * @param string $client_id
     * @return bool
     */
    public function isPublicClient($client_id)
    {
        /** @var Database_Result $queryResponse */
        $queryResponse = $this->db->select(
            'client_id',
            'client_secret',
            'redirect_uri',
            'grant_types',
            'scope'
        )
            ->from($this->config['client_table'])
            ->where('client_id', $client_id)
            ->execute();

        $result = $queryResponse->as_array()[0] ?? false;

        if (!$result) {
            return false;
        }

        return empty($result['client_secret']);
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getAuthorizationCode($code)
    {
        /** @var Database_Result $queryResponse */
        $queryResponse = $this->db->select('*')
            ->from($this->config['code_table'])
            ->where('authorization_code', $code)
            ->execute();

        $result = $queryResponse->as_array()[0] ?? false;

        if ($result !== false) {
            // convert date string back to timestamp
            $result['expires'] = strtotime($result['expires']);
        }

        return $result;
    }

    /**
     * @param string $code
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $redirect_uri
     * @param int    $expires
     * @param string $scope
     * @param string $id_token
     * @return bool|mixed
     */
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null, $code_challenge = null, $code_challenge_method = null)
    {
        if (func_num_args() > 6) {
            // we are calling with an id token
            return call_user_func_array(array($this, 'setAuthorizationCodeWithIdToken'), func_get_args());
        }

        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        $parameters = [
            'client_id' => $client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope,
            'code_challenge' => $code_challenge,
            'code_challenge_method' => $code_challenge_method,
        ];

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            $query = $this->db->update($this->config['code_table'])
                ->set($parameters)
                ->where('authorization_code', $code);

        } else {
            $parameters['code'] = $code;

            $query = $this->db->query(sprintf(
                'INSERT INTO %s (authorization_code, client_id, user_id, redirect_uri, expires, scope, code_challenge, code_challenge_method) 
                        VALUES (:code, :client_id, :user_id, :redirect_uri, :expires, :scope, :code_challenge, :code_challenge_method)',
                $this->config['code_table']
            ), \Fuel\Core\DB::INSERT)
                ->parameters($parameters);
        }

        return (bool) $query->execute();
    }

    /**
     * @param string $code
     * @return bool
     */
    public function expireAuthorizationCode($code)
    {
        $query = $this->db->delete($this->config['code_table'])
            ->where('authorization_code', $code);

        return (bool) $query->execute();
    }

    /**
     * @param string $access_token
     * @return array|bool|mixed|null
     */
    public function getAccessToken($access_token)
    {
        /** @var Database_Result $queryResponse */
        $queryResponse = $this->db->select('*')
            ->from($this->config['access_token_table'])
            ->where('access_token', $access_token)
            ->execute();

        $result = $queryResponse->as_array()[0] ?? false;

        if ($result !== false) {
            // convert date string back to timestamp
            $result['expires'] = strtotime($result['expires']);
        }

        return $result;
    }

    /**
     * @param string $access_token
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param int    $expires
     * @param string $scope
     * @return bool
     */
    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        $parameters = [
            'client_id' => $client_id,
            'expires' => $expires,
            'user_id' => $user_id,
            'scope' => $scope
        ];

        // if it exists, update it.
        if ($this->getAccessToken($access_token)) {
            $query = $this->db->update($this->config['access_token_table'])
                ->set($parameters)
                ->where('access_token', $access_token);

        } else {
            $parameters['access_token'] = $access_token;

           $query = $this->db->query(sprintf(
                'INSERT INTO %s (access_token, client_id, expires, user_id, scope) 
                        VALUES (:access_token, :client_id, :expires, :user_id, :scope)',
                $this->config['access_token_table']
           ), \Fuel\Core\DB::INSERT)
           ->parameters($parameters);
        }

        return (bool) $query->execute();
    }

    /**
     * @param string $refresh_token
     * @return bool|mixed
     */
    public function getRefreshToken($refresh_token)
    {
        /** @var Database_Result $queryResponse */
        $queryResponse = $this->db->select('*')
            ->from($this->config['refresh_token_table'])
            ->where('refresh_token', $refresh_token)
            ->execute();

        $result = $queryResponse->as_array()[0] ?? false;

        if ($result !== false) {
            // convert date string back to timestamp
            $result['expires'] = strtotime($result['expires']);
        }

        return $result;
    }

    /**
     * @param string $refresh_token
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param int $expires
     * @param string $scope
     * @return bool
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        $query = $this->db->query(sprintf(
            'INSERT INTO %s (refresh_token, client_id, user_id, expires, scope) 
                    VALUES (:refresh_token, :client_id, :user_id, :expires, :scope)',
            $this->config['refresh_token_table']
        ), \Fuel\Core\DB::INSERT)
            ->parameters([
                'refresh_token' => $refresh_token,
                'client_id' => $client_id,
                'expires' => $expires,
                'user_id' => $user_id,
                'scope' => $scope
            ]);

        return (bool) $query->execute();
    }

    /**
     * @param string $refresh_token
     * @return bool
     */
    public function unsetRefreshToken($refresh_token)
    {
        $query = $this->db->delete($this->config['refresh_token_table'])
            ->where('refresh_token', $refresh_token);

        return (bool) $query->execute();
    }

    /**
     * @param string $code
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $redirect_uri
     * @param int $expires
     * @param string $scope
     * @param string $id_token
     * @return bool
     */
    private function setAuthorizationCodeWithIdToken($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null, $code_challenge = null, $code_challenge_method = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        $parameters = [
            'client_id' => $client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope,
            'id_token' => $id_token,
            'code_challenge' => $code_challenge,
            'code_challenge_method' => $code_challenge_method,
        ];

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            $query = $this->db->update($this->config['code_table'])
                ->set($parameters)
                ->where('authorization_code', $code);
        } else {
            $parameters['code'] = $code;

            $query = $this->db->query(sprintf(
                'INSERT INTO %s (authorization_code, client_id, user_id, redirect_uri, expires, scope, id_token, code_challenge, code_challenge_method) 
                        VALUES (:code, :client_id, :user_id, :redirect_uri, :expires, :scope, :id_token, :code_challenge, :code_challenge_method)',
                $this->config['code_table']
            ), \Fuel\Core\DB::INSERT)
                ->parameters($parameters);
        }

        return (bool) $query->execute();
    }
}
