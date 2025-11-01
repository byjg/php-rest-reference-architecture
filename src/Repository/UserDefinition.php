<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\Authenticate\Model\UserModel;
use ByJG\MicroOrm\MapperFunctions\FormatSelectUuidMapper;
use ByJG\MicroOrm\MapperFunctions\FormatUpdateUuidMapper;
use RestReferenceArchitecture\Generator\FormatSelectUuid;
use RestReferenceArchitecture\Generator\FormatUpdateUuid;
use RestReferenceArchitecture\Generator\UuidSeedGenerator;

class UserDefinition extends \ByJG\Authenticate\Definition\UserDefinition
{
    public function __construct($table = 'users', $model = UserModel::class, $loginField = self::LOGIN_IS_USERNAME, $fieldDef = [])
    {
        parent::__construct($table, $model, $loginField, $fieldDef);

        $this->markPropertyAsReadOnly("uuid");
        $this->markPropertyAsReadOnly("created");
        $this->markPropertyAsReadOnly("updated");
        $this->defineGenerateKey(UuidSeedGenerator::class);

        $this->defineMapperForSelect("userid", FormatSelectUuidMapper::class);
        $this->defineMapperForUpdate('userid', FormatUpdateUuidMapper::class);
    }

}