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
     * @var ?string
     */
    protected ?string $id = null;

    /**
     * @OA\Property(type="string", format="string", nullable=true)
     * @var ?string
     */
    protected ?string $uuid = null;

    /**
     * @OA\Property(type="string", format="string", nullable=true)
     * @var ?string
     */
    protected ?string $field = null;



    /**
     * @return ?string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return DummyHex
     */
    public function setId(?string $id): DummyHex
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param string|null $uuid
     * @return DummyHex
     */
    public function setUuid(?string $uuid): DummyHex
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string|null $field
     * @return DummyHex
     */
    public function setField(?string $field): DummyHex
    {
        $this->field = $field;
        return $this;
    }


}
