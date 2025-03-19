<?php

namespace RestReferenceArchitecture\Model;

use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Authenticate\Model\UserModel;
use ByJG\Authenticate\Model\UserPropertiesModel;
use ByJG\MicroOrm\Attributes\TableAttribute;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use Exception;
use InvalidArgumentException;
use OpenApi\Attributes as OA;
use RestReferenceArchitecture\Psr11;

#[TableAttribute("users")]
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
     * @var ?string|int|HexUuidLiteral
     */
    #[OA\Property(type: "string", format: "string")]
    protected string|int|HexUuidLiteral|null $userid = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $name = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $email = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $username = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $password = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $created = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $updated = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $admin = null;

    /**
     * @OA\Property()
     * @var ?string
     */
    protected ?string $uuid = null;

    protected array $propertyList = [];

    /**
     * UserModel constructor.
     *
     * @param string $name
     * @param string $email
     * @param string $username
     * @param string $password
     * @param string $admin
     * @throws Exception
     */
    public function __construct(string $name = "", string $email = "", string $username = "", string $password = "", string $admin = "no")
    {
        parent::__construct($name, $email, $username, $password, $admin);

        $this->withPasswordDefinition(Psr11::get(PasswordDefinition::class));
    }


    /**
     * @return string|HexUuidLiteral|int|null
     */
    public function getUserid(): string|HexUuidLiteral|int|null
    {
        return $this->userid;
    }

    /**
     * @param string|HexUuidLiteral|int|null $userid
     */
    public function setUserid(string|HexUuidLiteral|int|null $userid): void
    {
        $this->userid = $userid;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        if (!empty($this->passwordDefinition) && !empty($password) && strlen($password) != 40) {
            $result = $this->passwordDefinition->matchPassword($password);
            if ($result != PasswordDefinition::SUCCESS) {
                throw new InvalidArgumentException("Password does not match the password definition [{$result}]");
            }
        }
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getCreated(): ?string
    {
        return $this->created;
    }

    /**
     * @param string|null $created
     */
    public function setCreated(?string $created): void
    {
        $this->created = $created;
    }

    /**
     * @return string|null
     */
    public function getAdmin(): ?string
    {
        return $this->admin;
    }

    /**
     * @param string|null $admin
     */
    public function setAdmin(?string $admin): void
    {
        $this->admin = $admin;
    }

    public function set(string $name, string|null $value): void
    {
        $property = $this->get($name, true);
        if (empty($property)) {
            $property = new UserPropertiesModel($name, $value ?? "");
            $this->addProperty($property);
        } else {
            $property->setValue($value);
        }
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
