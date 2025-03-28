<?php

namespace Test\Rest;

use ByJG\Authenticate\UsersDBDataset;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error422Exception;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Util\FakeApiRequester;

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
        $userRepo = Psr11::get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $user->set(User::PROP_RESETTOKEN, null);
        $user->set(User::PROP_RESETTOKENEXPIRE, null);
        $user->set(User::PROP_RESETCODE, null);
        $user->set(User::PROP_RESETALLOWED, null);
        $userRepo->save($user);

        // Check if the reset token was cleared
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertEmpty($user->get(User::PROP_RESETTOKEN));
        $this->assertEmpty($user->get(User::PROP_RESETTOKENEXPIRE));
        $this->assertEmpty($user->get(User::PROP_RESETCODE));
        $this->assertEmpty($user->get(User::PROP_RESETALLOWED));
        
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
        $userRepo = Psr11::get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKEN));
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKENEXPIRE));
        $this->assertNotEmpty($user->get(User::PROP_RESETCODE));
        $this->assertEmpty($user->get(User::PROP_RESETALLOWED));
    }

    public function testConfirmCodeFail()
    {
        $email = Credentials::getRegularUser()["username"];

        // Clear the reset token
        $userRepo = Psr11::get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKEN));
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKENEXPIRE));
        $this->assertNotEmpty($user->get(User::PROP_RESETCODE));
        $this->assertEmpty($user->get(User::PROP_RESETALLOWED));
        
        $this->expectException(Error422Exception::class);

        // Execute the request, expecting an error
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/login/confirmcode")
            ->withRequestBody(json_encode((["email" => $email, "code" => "123456", "token" => $user->get(User::PROP_RESETTOKEN)])))
            ->assertResponseCode(422)
        ;
        $this->assertRequest($request);
    }

    public function testConfirmCodeOk()
    {
        $email = Credentials::getRegularUser()["username"];

        // Clear the reset token
        $userRepo = Psr11::get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKEN));
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKENEXPIRE));
        $this->assertNotEmpty($user->get(User::PROP_RESETCODE));
        $this->assertEmpty($user->get(User::PROP_RESETALLOWED));
        
        // Execute the request, now with the correct code
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/login/confirmcode")
            ->withRequestBody(json_encode((["email" => $email, "code" => $user->get(User::PROP_RESETCODE), "token" => $user->get(User::PROP_RESETTOKEN)])))
            ->assertResponseCode(200)
        ;
        $this->assertRequest($request);

        // Check if the reset token was created
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKEN));
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKENEXPIRE));
        $this->assertNotEmpty($user->get(User::PROP_RESETCODE));
        $this->assertEquals(User::VALUE_YES, $user->get(User::PROP_RESETALLOWED));
    }

    public function testPasswordResetOk()
    {
        $email = Credentials::getRegularUser()["username"];
        $password = Credentials::getRegularUser()["password"];

        // Clear the reset token
        $userRepo = Psr11::get(UsersDBDataset::class);
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKEN));
        $this->assertNotEmpty($user->get(User::PROP_RESETTOKENEXPIRE));
        $this->assertNotEmpty($user->get(User::PROP_RESETCODE));
        $this->assertEquals(User::VALUE_YES, $user->get(User::PROP_RESETALLOWED));
        
        // Execute the request, now with the correct code
        $request = new FakeApiRequester();
        $request
            ->withPsr7Request($this->getPsr7Request())
            ->withMethod('POST')
            ->withPath("/login/resetpassword")
            ->withRequestBody(json_encode([
                "email" => $email,
                "token" => $user->get(User::PROP_RESETTOKEN),
                "password" => "new$password"
            ]))
            ->assertResponseCode(200)
        ;
        $this->assertRequest($request);

        // Check if the reset token was created
        $user = $userRepo->getByEmail($email);
        $this->assertNotNull($user);
        $this->assertEquals("83bfd34a3ebc0973609f5f2ec0080080286e3879", $user->getPassword());
        $this->assertEmpty($user->get(User::PROP_RESETTOKEN));
        $this->assertEmpty($user->get(User::PROP_RESETTOKENEXPIRE));
        $this->assertEmpty($user->get(User::PROP_RESETCODE));
        $this->assertEmpty($user->get(User::PROP_RESETALLOWED));

        // Restore old password
        $user->setPassword($password);
        $userRepo->save($user);
    }
}
