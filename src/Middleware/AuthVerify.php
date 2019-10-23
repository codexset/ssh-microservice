<?php
declare(strict_types=1);

namespace Hyperf\Support\Middleware;

use Hyperf\Extra\Contract\TokenServiceInterface;
use Hyperf\Extra\Contract\UtilsServiceInterface;
use Hyperf\Support\Redis\RefreshToken;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AuthVerify implements MiddlewareInterface
{
    protected $scene = 'default';
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var HttpResponse
     */
    private $response;
    /**
     * @var TokenServiceInterface
     */
    private $token;
    /**
     * @var UtilsServiceInterface
     */
    private $utils;

    public function __construct(
        ContainerInterface $container,
        HttpResponse $response
    )
    {
        $this->container = $container;
        $this->response = $response;
        $this->token = $container->get(TokenServiceInterface::class);
        $this->utils = $container->get(UtilsServiceInterface::class);
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $tokenString = $request->getCookieParams()[$this->scene . '_token'];
            $result = $this->token->verify($this->scene, $tokenString);
            if ($result->expired) {
                /**
                 * @var $token \Lcobucci\JWT\Token
                 */
                $token = $result->token;
                $jti = $token->getClaim('jti');
                $ack = $token->getClaim('ack');
                $verify = RefreshToken::create($this->container)->verify($jti, $ack);
                if (!$verify) {
                    return $this->response->json([
                        'error' => 1,
                        'msg' => 'refresh token verification expired'
                    ]);
                }
                $symbol = (array)$token->getClaim('symbol');
                $preTokenString = (string)$this->token->create(
                    $this->scene . '_token',
                    $jti,
                    $ack,
                    $symbol
                );
                $cookie = $this->utils->cookie($this->scene . '_token', $preTokenString);
                var_dump(Context::get(RequestHandlerInterface::class));
            }

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->response->json([
                'error' => 1,
                'msg' => $e->getMessage()
            ]);
        }
    }
}