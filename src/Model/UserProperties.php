<?php

namespace RestReferenceArchitecture\Model;

use ByJG\Authenticate\Model\UserPropertiesModel;
use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\FieldUuidAttribute;
use ByJG\MicroOrm\Attributes\TableAttribute;
use ByJG\MicroOrm\Literal\Literal;

#[TableAttribute(tableName: 'users_property')]
class UserProperties extends UserPropertiesModel
{
    #[FieldUuidAttribute]
    protected string|int|Literal|null $userid = null;

    #[FieldAttribute(primaryKey: true)]
    protected ?string $id = null;

    #[FieldAttribute]
    protected ?string $name = null;

    #[FieldAttribute]
    protected ?string $value = null;
}