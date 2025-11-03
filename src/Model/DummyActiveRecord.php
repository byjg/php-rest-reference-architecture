<?php

namespace RestReferenceArchitecture\Model;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\TableAttribute;
use ByJG\MicroOrm\Trait\ActiveRecord;
use OpenApi\Attributes as OA;
use RestReferenceArchitecture\Trait\OaCreatedAt;
use RestReferenceArchitecture\Trait\OaUpdatedAt;

/**
 * DummyActiveRecord Model using ActiveRecord pattern
 */
#[OA\Schema(schema: "DummyActiveRecord", required: ["name"])]
#[TableAttribute(tableName: "dummy_active_record")]
class DummyActiveRecord
{
    // Add the ActiveRecord trait to enable Active Record pattern
    use ActiveRecord;

    // Add timestamp traits for automatic timestamp handling
    use OaCreatedAt;
    use OaUpdatedAt;

    /**
     * @var int|null
     */
    #[OA\Property(type: "integer", format: "int32")]
    #[FieldAttribute(primaryKey: true)]
    protected ?int $id = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute]
    protected ?string $name = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string", nullable: true)]
    #[FieldAttribute]
    protected ?string $value = null;

    // Note: created_at is provided by the CreatedAt trait

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return DummyActiveRecord
     */
    public function setId(?int $id): DummyActiveRecord
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return DummyActiveRecord
     */
    public function setName(?string $name): DummyActiveRecord
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return DummyActiveRecord
     */
    public function setValue(?string $value): DummyActiveRecord
    {
        $this->value = $value;
        return $this;
    }
}
