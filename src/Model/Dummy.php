<?php

namespace RestReferenceArchitecture\Model;
use OpenApi\Attributes as OA;

/**
 * Class Dummy
 * @package RestReferenceArchitecture\Model
 */
#[OA\Schema(required: ["id", "field"], type: "object", xml: new OA\Xml(name: "Dummy"))]
class Dummy
{

    /**
     * @var int|null
     */
    #[OA\Property(type: "integer", format: "int32")]
    protected ?int $id = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    protected ?string $field = null;



    /**
     * @return int|null
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
     * @return string|null
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
