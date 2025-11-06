<?php

namespace RestReferenceArchitecture\Model;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\FieldUuidAttribute;
use ByJG\MicroOrm\Attributes\TableMySqlUuidPKAttribute;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use ByJG\MicroOrm\Literal\Literal;
use OpenApi\Attributes as OA;


/**
 * Class DummyHex
 * @package RestReferenceArchitecture\Model
 */
#[OA\Schema(required: ["id", "field"], type: "object", xml: new OA\Xml(name: "DummyHex"))]
#[TableMySqlUuidPKAttribute("dummyhex")]
class DummyHex
{

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldUuidAttribute(primaryKey: true, fieldName: "id")]
    protected string|HexUuidLiteral|null $id = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string", nullable: true)]
    #[FieldAttribute(fieldName: "uuid", syncWithDb: false)]
    protected string|null $uuid = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute(fieldName: "field")]
    protected string|null $field = null;



    /**
     * @return string|HexUuidLiteral|null
     */
    public function getId(): string|HexUuidLiteral|null
    {
        return $this->id;
    }

    /**
     * @param string|Literal|null $id
     * @return $this
     */
    public function setId(string|Literal|null $id): static
    {
        if ($id instanceof Literal) {
            $id = new HexUuidLiteral($id);
        }
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUuid(): string|null
    {
        return $this->uuid;
    }

    /**
     * @param string|null $uuid
     * @return $this
     */
    public function setUuid(string|null $uuid): static
    {
        
        $this->uuid = $uuid;
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
