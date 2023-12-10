<?php

namespace RestReferenceArchitecture\Model;

use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Authenticate\Model\UserModel;
use Exception;
use OpenApi\Attributes as OA;
use RestReferenceArchitecture\Psr11;

#[OA\Schema(required: ["email"], type: "object", xml: new OA\Xml(name: "User"))]
class User extends UserModel
{
    // Property Fields
    const PROP_RESETTOKENEXPIRE = 'resettokenexpire';
    const PROP_RESETTOKEN = 'resettoken';
    const PROP_RESETCODE = 'resetcode';
    const PROP_RESETALLOWED = 'resetallowed';

    // Property Values
    const VALUE_YES = 'yes';
    const VALUE_NO = 'no';

    // Roles
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $userid;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $name;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $email;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $username;
    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $password;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $created;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $updated;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected $admin = "no";

    /**
     * @OA\Property()
     * @var ?string
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
