<?php

namespace RestTemplate\Model;

use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Authenticate\Model\UserModel;
use Exception;
use RestTemplate\Psr11;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(required={"email"}, type="object", @OA\Xml(name="User"))
 */
class User extends UserModel
{
    /**
     * @OA\Property()
     * @var string
     */
    protected $userid;
    /**
     * @OA\Property()
     * @var string
     */
    protected $name;
    /**
     * @OA\Property()
     * @var string
     */
    protected $email;
    /**
     * @OA\Property()
     * @var string
     */
    protected $username;
    /**
     * @OA\Property()
     * @var string
     */
    protected $password;
    /**
     * @OA\Property()
     * @var string
     */
    protected $created;
    /**
     * @OA\Property()
     * @var string
     */
    protected $updated;
    /**
     * @OA\Property()
     * @var string
     */
    protected $admin = "no";

    /**
     * @OA\Property()
     * @var string
     */
    protected $uuid;

    /**
     * User constructor.
     * @param string $name
     * @param string $email
     * @param string $username
     * @param string $password
     * @param string $admin
     * @throws Exception
     */
    public function __construct(string $name = "", string $email = "", string $username = "", string $password = "", string $admin = "")
    {
        parent::__construct($name, $email, $username, $password, $admin);
        
        $this->withPasswordDefinition(Psr11::container()->get(PasswordDefinition::class));
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }
}
