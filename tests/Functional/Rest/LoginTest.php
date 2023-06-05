<?php

namespace Test\Functional\Rest;

use ByJG\Authenticate\UsersDBDataset;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error422Exception;
use RestTemplate\Psr11;
use RestTemplate\Util\FakeApiRequester;

/**
 * Create a TestCase inherited from SwaggerTestCase
 */
class LoginTest extends BaseApiTestCase
{

    public function testLoginOk()
    {
        $this->assertRequest(Credentials::requestLogin(Credentials::getAdminUser()));
    }

    public function testLoginOk2()
    {
        $this->assertRequest(Credentials::requestLogin(Credentials::getRegularUser()));
    }

    public function testLoginFail()
    {
        $this->expectException(Error401Exception::class);
        $this->expectExceptionMessage('Username or password is invalid');

        $this->assertRequest(Credentials::requestLogin([
            'username' => 'invalid',
            'password' => 'invalid'
        ]));
    }

    public function testResetRequestOk()
    {
        $email = Credentials::getRegularUser()["username"];

        // Clear the reset token
        $userRepo = Psr11::container()->get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $user->set("resettoken", null);
        $user->set("resettokenexpire", null);
        $user->set("resetcode", null);
        $user->set("resetallowed", null);
        $userRepo->save($user);

        // Check if the reset token was cleared
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertEmpty($user->get("resettoken"));
        $this->assertEmpty($user->get("resettokenexpire"));
        $this->assertEmpty($user->get("resetcode"));
        $this->assertEmpty($user->get("resetallowed"));
        
        // Execute the request
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/login/resetrequest")
            ->withRequestBody(json_encode(["email" => $email]))
            ->assertResponseCode(200)
        ;
        $this->assertRequest($request);

        // Check if the reset token was created
        $userRepo = Psr11::container()->get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get("resettoken"));
        $this->assertNotEmpty($user->get("resettokenexpire"));
        $this->assertNotEmpty($user->get("resetcode"));
        $this->assertEmpty($user->get("resetallowed"));
    }

    public function testConfirmCodeFail()
    {
        $email = Credentials::getRegularUser()["username"];

        // Clear the reset token
        $userRepo = Psr11::container()->get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get("resettoken"));
        $this->assertNotEmpty($user->get("resettokenexpire"));
        $this->assertNotEmpty($user->get("resetcode"));
        $this->assertEmpty($user->get("resetallowed"));
        
        $this->expectException(Error422Exception::class);

        // Execute the request, expecting an error
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/login/confirmcode")
            ->withRequestBody(json_encode((["email" => $email, "code" => "123456", "token" => $user->get("resettoken")])))
            ->assertResponseCode(422)
        ;
        $this->assertRequest($request);
    }

    public function testConfirmCodeOk()
    {
        $email = Credentials::getRegularUser()["username"];

        // Clear the reset token
        $userRepo = Psr11::container()->get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get("resettoken"));
        $this->assertNotEmpty($user->get("resettokenexpire"));
        $this->assertNotEmpty($user->get("resetcode"));
        $this->assertEmpty($user->get("resetallowed"));
        
        // Execute the request, now with the correct code
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/login/confirmcode")
            ->withRequestBody(json_encode((["email" => $email, "code" => $user->get("resetcode"), "token" => $user->get("resettoken")])))
            ->assertResponseCode(200)
        ;
        $this->assertRequest($request);

        // Check if the reset token was created
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get("resettoken"));
        $this->assertNotEmpty($user->get("resettokenexpire"));
        $this->assertNotEmpty($user->get("resetcode"));
        $this->assertEquals("yes", $user->get("resetallowed"));
    }

    public function testPasswordResetOk()
    {
        $email = Credentials::getRegularUser()["username"];
        $password = Credentials::getRegularUser()["password"];

        // Clear the reset token
        $userRepo = Psr11::container()->get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get("resettoken"));
        $this->assertNotEmpty($user->get("resettokenexpire"));
        $this->assertNotEmpty($user->get("resetcode"));
        $this->assertEquals("yes", $user->get("resetallowed"));
        
        // Execute the request, now with the correct code
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/login/resetpassword")
            ->withRequestBody(json_encode((["email" => $email, "token" => $user->get("resettoken"), "password" => "new$password"])))
            ->assertResponseCode(200)
        ;
        $this->assertRequest($request);

        // Check if the reset token was created
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertEquals("83bfd34a3ebc0973609f5f2ec0080080286e3879", $user->getPassword());
        $this->assertEmpty($user->get("resettoken"));
        $this->assertEmpty($user->get("resettokenexpire"));
        $this->assertEmpty($user->get("resetcode"));
        $this->assertEmpty($user->get("resetallowed"));

        // Restore old password
        $user->setPassword($password);
        $userRepo->save($user);
    }
}
