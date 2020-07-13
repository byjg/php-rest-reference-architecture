<?php

namespace Test\Functional\Rest;

use ByJG\ApiTools\Base\Schema;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class LoginTest extends BaseApiTestCase
{
    protected $filePath = __DIR__ . '/../../../web/docs/swagger.json';

    public function setUp()
    {
        $schema = Schema::getInstance(file_get_contents($this->filePath));
        $this->setSchema($schema);
    }
    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \ByJG\Util\Psr7\MessageException
     */
    public function testLoginOk()
    {
        $this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()));
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \ByJG\Util\Psr7\MessageException
     */
    public function testLoginOk2()
    {
        $this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()));
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     * @throws \ByJG\Util\Psr7\MessageException
     * @expectedException \ByJG\RestServer\Exception\Error401Exception
     * @expectedExceptionMessage Username or password is invalid
     */
    public function testLoginFail()
    {
        $this->assertRequest(Credentials::requestLogin([
            'username' => 'invalid',
            'password' => 'invalid'
        ]));
    }
}
