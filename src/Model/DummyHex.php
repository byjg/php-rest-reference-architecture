<?php

namespace RestReferenceArchitecture\Model;
use OpenApi\Attributes as OA;

/**
 * Class DummyHex
 * @package RestReferenceArchitecture\Model
 */
#[OA\Schema(required: ["id", "field"], type: "object", xml: new OA\Xml(name: "DummyHex"))]
class DummyHex
{

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $id = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string", nullable: true)]
    protected ?string $uuid = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $field = null;



    /**
     * @return string|null
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
     * @return string|null
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
     * @return string|null
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
