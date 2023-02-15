<?php

namespace RestTemplate\Model;

use OpenApi\Annotations as OA;

// PHPDOC for Dummy class using OpenAPI Zircote annotations
/**
 * Class Dummy
 * @package RestTemplate\Model
 * @OA\Schema(required={"field"}, type="object", @OA\Xml(name="Dummy"))
 */
class Dummy
{
    /**
     * The "fake" key
     * @OA\Property()
     * @var int
     */
    protected $id;

    /**
     * Some field property
     * @OA\Property()
     * @var string
     */
    protected $field;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }



    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
}
