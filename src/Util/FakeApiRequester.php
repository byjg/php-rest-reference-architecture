<?php


namespace RestReferenceArchitecture\Util;

use ByJG\ApiTools\AbstractRequester;
use ByJG\Config\Config;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\Config\Exception\RunTimeException;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\Exception\Error405Exception;
use ByJG\RestServer\Exception\Error422Exception;
use ByJG\RestServer\Exception\Error520Exception;
use ByJG\RestServer\Exception\OperationIdInvalidException;
use ByJG\RestServer\Middleware\JwtMiddleware;
use ByJG\RestServer\MockRequestHandler;
use ByJG\RestServer\MockServer;
use ByJG\RestServer\Route\OpenApiRouteList;
use ByJG\WebRequest\Exception\MessageException;
use ByJG\WebRequest\Exception\RequestException;
use ByJG\WebRequest\MockClient;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;


/**
 * Request handler based on ByJG HttpClient (WebRequest) .
 */
class FakeApiRequester extends AbstractRequester
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ConfigException
     * @throws DependencyInjectionException
     * @throws Error404Exception
     * @throws Error405Exception
     * @throws Error520Exception
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws RequestException
     * @throws RunTimeException
     * @throws Error422Exception
     * @throws OperationIdInvalidException
     * @throws MessageException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Override]
    protected function handleRequest(RequestInterface $request): ResponseInterface
    {
        $mock = new MockServer(Config::get(LoggerInterface::class));
        $mock->withMiddleware(Config::get(JwtMiddleware::class));
        $mock->withRequestObject($request);
        $mock->handle(Config::get(OpenApiRouteList::class), false, false);

        $httpClient = new MockClient($mock->getPsr7Response());
        return $httpClient->sendRequest($request);
    }
}
