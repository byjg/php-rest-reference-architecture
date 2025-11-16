<?php

namespace RestReferenceArchitecture\Model;

use ByJG\Authenticate\Definition\PasswordDefinition;
use ByJG\Authenticate\MapperFunctions\PasswordSha1Mapper;
use ByJG\Authenticate\Model\UserModel;
use ByJG\Config\Config;
use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\FieldUuidAttribute;
use ByJG\MicroOrm\Attributes\TableMySqlUuidPKAttribute;
use ByJG\MicroOrm\Literal\Literal;
use Exception;
use OpenApi\Attributes as OA;

#[TableMySqlUuidPKAttribute("users")]
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
     * @var ?string|int|Literal
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldUuidAttribute(primaryKey: true)]
    protected string|int|Literal|null $userid = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute]
    protected ?string $name = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute]
    protected ?string $email = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute]
    protected ?string $username = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute(updateFunction: PasswordSha1Mapper::class)]
    protected ?string $password = null;

    /**
     * @var ?string
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute]
    protected ?string $role = null;

    protected array $propertyList = [];

    /**
     * UserModel constructor.
     *
     * @param string $name
     * @param string $email
     * @param string $username
     * @param string $password
     * @param string $role
     * @throws Exception
     */
    public function __construct(string $name = "", string $email = "", string $username = "", string $password = "", string $role = "")
    {
        parent::__construct($name, $email, $username, $password, $role);

        $this->withPasswordDefinition(Config::get(PasswordDefinition::class));
    }
}
