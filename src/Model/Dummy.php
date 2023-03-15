<?php

namespace RestTemplate\Model;

/**
 * Class Dummy
 * @package RestTemplate\Model
 * @OA\Schema(required={"id"}, type="object", @OA\Xml(name="Dummy"))
 */
class Dummy
{

    /**
     * @OA\Property()
     * @var int
     */
    protected $id;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    protected $field;



    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Dummy
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return Dummy
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }


}
