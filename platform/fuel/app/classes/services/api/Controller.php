<?php

namespace Services\Api;

use Container;
use Exception;
use Fuel\Core\Controller_Rest;
use Fuel\Core\Input;
use Fuel\Core\Request;
use Fuel\Core\Response;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;

abstract class Controller extends Controller_Rest
{
    protected Whitelabel $whitelabel;

    private string $nonce = "";

    private string $signature = "";

    private string $key = "";

    private array $get = [];

    private array $headers = [];

    private string $checksum;

    /** @var string[] $_supported_formats */
    protected $_supported_formats = [
        'xml' => 'application/xml',
        'json' => 'application/json',
        'jsonp' => 'text/javascript',
        'serialized' => 'application/vnd.php.serialized',
        'php' => 'text/plain',
    ];

    private Security $security;

    protected Reply $reply;

    private Logger $logger;

    private array $params = [];

    /** @var Response|null */
    private $error_response = null;

    public function __construct(\Request $request)
    {
        parent::__construct($request);
    }

    public function before()
    {
        parent::before();

        $this->createSecurity();
        $this->createReply();
        $this->createLogger();

        $this->setErrorResponse(
            Reply::NOT_FOUND,
            ["Api endpoint not found"]
        );
    }

    /** Extract to separate function only on testing purposes */
    public function createSecurity(): void
    {
        /** @var WhitelabelRepository $whitelabelRepository */
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->security = new Security($whitelabelRepository);
    }

    /** Extract to separate function only on testing purposes */
    public function createReply(): void
    {
        $this->reply = new Reply();
    }

    /** Extract to separate function only on testing purposes */
    public function createLogger(): void
    {
        $this->logger = new Logger();
    }

    /**
     * @param string $method
     * @param array $params
     * @return bool|Request|Response|mixed
     * @throws Exception
     */
    public function router($method, $params)
    {
        $this->params = $params;

        if ($this->security->isApiRouteNotAccessible()) {
            return Request::forge('index/404')->execute();
        }

        if ($this->security->checkUrlNotBeginFromApi()) {
            $this->setErrorResponse(
                Reply::UNAUTHORIZED,
                ["Bad API endpoint"]
            );

            return $this->breakRouterWithErrors();
        }

        /** @var ?Whitelabel $whitelabel */
        $whitelabel = $this->security->getWhitelabel();

        if (empty($whitelabel)) {
            $this->setErrorResponse(
                Reply::UNAUTHORIZED,
                ["Whitelabel not exists"]
            );

            return $this->breakRouterWithErrors();
        }

		$this->whitelabel = $whitelabel;
        $this->logger
            ->setHeaders($this->headers)
            ->setSignature($this->signature)
            ->setWhitelabel($this->whitelabel);

        if ($this->security->checkIpNotExist($this->whitelabel)) {
            $this->setErrorResponse(
                Reply::UNAUTHORIZED,
                ["Bad remote IP"]
            );

            return $this->breakRouterWithErrors();
        }

        $this->setNewHeaders();

        $credentials_not_valid = empty($this->nonce) || empty($this->signature) || empty($this->key);

        if ($credentials_not_valid) {
            $this->setErrorResponse(
                Reply::UNAUTHORIZED,
                ["Nonce, signature or key is not present"]
            );

            return $this->breakRouterWithErrors();
        }

        $this->nonce = intval($this->nonce);

        $this->logger->setNonce($this->nonce)->setKey($this->key);

        if ($this->security->checkNonceExist($this->nonce, $this->whitelabel)) {
            $this->setErrorResponse(
                Reply::UNAUTHORIZED,
                ["This nonce is already used"]
            );

            return $this->breakRouterWithErrors();
        }

        if ($this->security->isWhitelabelWithApiKeyNotExist($this->key, $this->whitelabel))
        {
            $this->setErrorResponse(
                Reply::UNAUTHORIZED,
                ["Bad API key"]
            );

            return $this->breakRouterWithErrors();
        }

        $api = $this->security->getWhitelabelApi($this->key, $this->whitelabel);

        $api = $api[0];

        $this->setNewGetKeys();

        $api_secret = $api['api_secret'];

        $this->checksum = $this->security->getChecksum(
            $this->nonce,
            $this->get,
            $api_secret
        );

        $this->logger->setChecksum($this->checksum);

        $correct_check_sum = $this->checksum !== $this->signature;

        if ($correct_check_sum) {
            $this->setErrorResponse(
                Reply::UNAUTHORIZED,
                ["Bad signature"]
            );

            return $this->breakRouterWithErrors();
        }
        parent::router($method, $params);
    }

    /**
     * @return object
     */
    public function action_error_response(): object
    {
        return $this->response($this->error_response['response'], $this->error_response['status']);
    }

    /**
     * @param array $message
     * @param array $status
     * @return Response
     */
    public function returnResponse(array $message, array $status = Reply::OK): Response
    {
        switch ($status) {
            case Reply::OK:
                $response = $this->reply->buildResponseOk($message);
                break;

            default:
                $response = $this->getErrorResponse($status, $message);
        }

        $this->logger->logData($status, $message);

        /** @var Response $response */
        $response = $this->response($response, $status['status']);

        return $response;
    }

    /**
     * @param string $basenode
     */
    public function setXmlBasenode(string $basenode): void
    {
        $this->xml_basenode = $basenode;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @param array $status
     * @param array $message
     */
    private function setErrorResponse(array $status, array $message): void
    {
        $this->error_response = $this->reply->buildResponseError($status, $message);
    }

    /**
     * @param array $status
     * @param array $message
     * @return array
     */
    private function getErrorResponse(array $status, array $message): array
    {
        $response = $this->reply->buildResponseError($status, $message);

        return $response['response'];
    }

    private function breakRouterWithErrors()
    {
        parent::router('error_response', $this->params);
        return;
    }

    private function setNewHeaders(): void
    {
        // disabled on CGI
        // $headers = getallheaders();
        // thank you fuelphp!
        $this->headers = \Input::headers();

        foreach ($this->headers as $name => $header) {
            $lower_name = strtolower($name);

            switch ($lower_name) {
                case 'x-whitelotto-key':
                case 'x-whitelotto-nonce':
                case 'x-whitelotto-signature':
                    $name = explode('-', $lower_name);
                    $this->{$name[2]} = $header;
                    break;
            }
        }
    }

    private function setNewGetKeys(): void
    {
        foreach (Input::get() as $key => $iget) {
            $this->get[$key] = $iget;
        }
    }
}