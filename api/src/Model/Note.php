<?php

namespace RestReferenceArchitecture\Model;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\FieldUuidAttribute;
use ByJG\MicroOrm\Attributes\TableAttribute;
use ByJG\MicroOrm\Attributes\TableMySqlUuidPKAttribute;
use ByJG\MicroOrm\Literal\LiteralInterface;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Trait\ActiveRecord;
use RuntimeException;
use OpenApi\Attributes as OA;
use ByJG\Gluo\Trait\OaCreatedAt;
use ByJG\Gluo\Trait\OaUpdatedAt;


/**
 * Class Note
 * @package RestReferenceArchitecture\Model
 */
#[OA\Schema(required: ["id", "body"], type: "object", xml: new OA\Xml(name: "Note"))]
#[TableAttribute("note")]
class Note
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
    #[OA\Property(type: "string", format: "string", nullable: true)]
    #[FieldAttribute(fieldName: "task_uuid")]
    protected string|null $taskUuid = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute(fieldName: "body")]
    protected string|null $body = null;



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
    public function getTaskUuid(): string|null
    {
        return $this->taskUuid;
    }

    /**
     * @param string|null $taskUuid
     * @return $this
     */
    public function setTaskUuid(string|null $taskUuid): static
    {
        
        $this->taskUuid = $taskUuid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody(): string|null
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     * @return $this
     */
    public function setBody(string|null $body): static
    {
        
        $this->body = $body;
        return $this;
    }



    /**
     * @param mixed $taskUuid
     * @return null|Note[]
     */
    public static function getByTaskUuid($taskUuid): ?array
    {
        if (self::$repository === null) {
            throw new RuntimeException("Repository not initialized");
        }
        $query = Query::getInstance()
            ->table(self::$repository->getMapper()->getTable(), 'alias')
            ->where('alias.task_uuid = :value', ['value' => $taskUuid]);
        return self::query($query);
    }

}
