<?php

namespace RestReferenceArchitecture\Model;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\TableAttribute;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Trait\ActiveRecord;
use OpenApi\Attributes as OA;
use RestReferenceArchitecture\Trait\OaCreatedAt;
use RestReferenceArchitecture\Trait\OaUpdatedAt;


/**
 * Class DummyActiveRecord
 * @package RestReferenceArchitecture\Model
 */
#[OA\Schema(required: ["id", "name"], type: "object", xml: new OA\Xml(name: "DummyActiveRecord"))]
#[TableAttribute("dummy_active_record")]
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
    #[FieldAttribute(primaryKey: true, fieldName: "id")]
    protected int|null $id = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute(fieldName: "name")]
    protected string|null $name = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string", nullable: true)]
    #[FieldAttribute(fieldName: "value")]
    protected string|null $value = null;



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
    public function getName(): string|null
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(string|null $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): string|null
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setValue(string|null $value): static
    {
        $this->value = $value;
        return $this;
    }



    /**
     * @param mixed $name
     * @return null|DummyActiveRecord[]
     */
    public static function getByName($name): ?array
    {
        $query = Query::getInstance()
            ->table(self::$repository->getMapper()->getTable(), 'alias')
            ->where('alias.name = :value', ['value' => $name]);
        return self::query($query);
    }

}
