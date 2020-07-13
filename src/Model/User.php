<?php

namespace RestTemplate\Model;

use ByJG\Authenticate\Model\UserModel;
use Exception;

/**
 * @SWG\Definition(required={"email"}, type="object", @SWG\Xml(name="User"))
 */
class User extends UserModel
{
    /**
     * @SWG\Property()
     * @var string
     */
    protected $userid;
    /**
     * @SWG\Property()
     * @var string
     */
    protected $name;
    /**
     * @SWG\Property()
     * @var string
     */
    protected $email;
    /**
     * @SWG\Property()
     * @var string
     */
    protected $username;
    /**
     * @SWG\Property()
     * @var string
     */
    protected $password;
    /**
     * @SWG\Property()
     * @var string
     */
    protected $created;
    /**
     * @SWG\Property()
     * @var string
     */
    protected $updated;
    /**
     * @SWG\Property()
     * @var string
     */
    protected $admin = "no";

    /**
     * @SWG\Property()
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
