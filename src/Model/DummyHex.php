<?php

namespace RestTemplate\Model;

/**
 * Class DummyHex
 * @package RestTemplate\Model
 * @OA\Schema(required={"id"}, type="object", @OA\Xml(name="DummyHex"))
 */
class DummyHex
{

    /**
     * @OA\Property(type="string", format="string")
     * @var string
     */
    protected $id;

    /**
     * @OA\Property(type="string", format="string", nullable=true)
     * @var string
     */
    protected $uuid;

    /**
     * @OA\Property(type="string", format="string", nullable=true)
     * @var string
     */
    protected $field;



    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return DummyHex
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return DummyHex
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return DummyHex
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }


}
