<?php


namespace RestTemplate\Util;

use ByJG\ApiTools\AbstractRequester;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\RestServer\Exception\ClassNotFoundException;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\Exception\Error405Exception;
use ByJG\RestServer\Exception\Error520Exception;
use ByJG\RestServer\Exception\InvalidClassException;
use ByJG\RestServer\MockRequestHandler;
use ByJG\RestServer\Route\OpenApiRouteList;
use ByJG\Util\MockClient;
use ByJG\Util\Psr7\MessageException;
use ByJG\Util\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestTemplate\Psr11;


/**
 * Request handler based on ByJG HttpClient (WebRequest) .
 */
class FakeApiRequester extends AbstractRequester
{
    /**
     * @param RequestInterface $request
     * @return Response|ResponseInterface
     * @throws ClassNotFoundException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error404Exception
     * @throws Error405Exception
     * @throws Error520Exception
     * @throws InvalidArgumentException
     * @throws InvalidClassException
     * @throws KeyNotFoundException
     * @throws MessageException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws InvalidDateException
     */
    protected function handleRequest(RequestInterface $request)
    {
        $mock = MockRequestHandler::mock(Psr11::container()->get(OpenApiRouteList::class), $request);

        $httpClient = new MockClient($mock->getPsr7Response());
        return $httpClient->sendRequest($request);
    }
}
