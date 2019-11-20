<?php

namespace RestTemplate\Model;

/**
 * Model that represents the DummyHex table
 *
 * @SWG\Definition(required={"field"}, type="object", @SWG\Xml(name="DummyHex"))
 */
class DummyHex
{
    /**
     * The "fake" key
     * @SWG\Property()
     * @var string
     */
    protected $id;

    /**
     * The UUID
     * @SWG\Property()
     * @var string
     */
    protected $uuid;

    /**
     * Some field property
     * @SWG\Property()
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
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
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
