<?php

namespace RestTemplate\Model;

/**
 * Model that represents the Dummy table
 *
 * @SWG\Definition(required={"field"}, type="object", @SWG\Xml(name="Dummy"))
 */
class Dummy
{
    /**
     * @SWG\Property()
     * @var int
     */
    protected $id;

    /**
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
