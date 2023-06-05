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
     * @OA\Property(type="integer", format="int32")
     * @var ?int
     */
    protected ?int $id = null;

    /**
     * @OA\Property(type="string", format="string", nullable=true)
     * @var string|null
     */
    protected ?string $field = null;



    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Dummy
     */
    public function setId(?int $id): Dummy
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string|null $field
     * @return Dummy
     */
    public function setField(?string $field): Dummy
    {
        $this->field = $field;
        return $this;
    }


}
