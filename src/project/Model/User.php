<?php

namespace RestTemplate\Model;

use ByJG\Authenticate\Model\UserModel;

class User extends UserModel
{
    protected $uuid;

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
