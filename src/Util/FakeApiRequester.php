<?php


namespace RestReferenceArchitecture\Util;

use ByJG\ApiTools\AbstractRequester;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Middleware\JwtMiddleware;
use ByJG\RestServer\MockRequestHandler;
use ByJG\RestServer\Route\OpenApiRouteList;
use ByJG\Util\Exception\MessageException;
use ByJG\Util\MockClient;
use ByJG\Util\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestReferenceArchitecture\Psr11;


/**
 * Request handler based on ByJG HttpClient (WebRequest) .
 */
class FakeApiRequester extends AbstractRequester
{
    /**
     * @param RequestInterface $request
     * @return Response|ResponseInterface
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws MessageException
     */
    protected function handleRequest(RequestInterface $request)
    {
        $mock = new MockRequestHandler(Psr11::container()->get(LoggerInterface::class));
        $mock->withMiddleware(Psr11::container()->get(JwtMiddleware::class));
        $mock->withRequestObject($request);
        $mock->handle(Psr11::container()->get(OpenApiRouteList::class), false, false);

        $httpClient = new MockClient($mock->getPsr7Response());
        return $httpClient->sendRequest($request);
    }
}
