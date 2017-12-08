<?php

namespace RestTemplate\Model;

class UserDefinition extends \ByJG\Authenticate\Definition\UserDefinition
{
    protected $uuid = 'uuid';

    /**
     * Class Constructor
     *
     * @param string $table
     * @param string $loginField
     * @param array $fieldDef
     */
    public function __construct(
        $table = 'users',
        $loginField = self::LOGIN_IS_USERNAME,
        array $fieldDef = []
    ) {
        // Remember to call the parent
        parent::__construct($table, $loginField, $fieldDef);

        // Set the Model class
        $this->model = User::class;
    }

    /**
     * This will be set the mapping;
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
