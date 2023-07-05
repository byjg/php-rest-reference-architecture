<?php

namespace RestTemplate\Model;

use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Authenticate\Model\UserModel;
use Exception;
use OpenApi\Attributes as OA;
use RestTemplate\Psr11;

#[OA\Schema(required: ["email"], type: "object", xml: new \OpenApi\Attributes\Xml(name: "User"))]
class User extends UserModel
{
    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $updated = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $uuid = null;

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
     * @return ?string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid(?string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUpdated(): ?string
    {
        return $this->updated;
    }

    public function setUpdated(?string $updated)
    {
        $this->updated = $updated;
    }
}
