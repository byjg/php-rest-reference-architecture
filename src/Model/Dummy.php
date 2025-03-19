<?php

namespace RestReferenceArchitecture\Model;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\TableAttribute;
use OpenApi\Attributes as OA;

/**
 * Class Dummy
 * @package RestReferenceArchitecture\Model
 */
#[OA\Schema(required: ["id", "field"], type: "object", xml: new OA\Xml(name: "Dummy"))]
#[TableAttribute("dummy")]
class Dummy
{

    /**
     * @var int|null
     */
    #[OA\Property(type: "integer", format: "int32")]
    #[FieldAttribute(primaryKey: true, fieldName: "id")]
    protected int|null $id = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute(fieldName: "field")]
    protected string|null $field = null;



    /**
     * @return int|null
     */
    public function getId(): int|null
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(int|null $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getField(): string|null
    {
        return $this->field;
    }

    /**
     * @param string|null $field
     * @return $this
     */
    public function setField(string|null $field): static
    {
        $this->field = $field;
        return $this;
    }


}
