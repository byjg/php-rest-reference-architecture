<?php

namespace RestReferenceArchitecture\Service;

use ByJG\AnyDataset\Core\Exception\DatabaseException;
use ByJG\AnyDataset\Db\Exception\DbDriverNotConnected;
use ByJG\MicroOrm\Exception\InvalidArgumentException;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\MicroOrm\Exception\RepositoryReadOnlyException;
use ByJG\MicroOrm\Exception\UpdateConstraintException;
use ByJG\MicroOrm\Literal\LiteralInterface;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\Exception\Error422Exception;
use ByJG\Serializer\ObjectCopy;
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use RestReferenceArchitecture\Repository\BaseRepository;

abstract class BaseService
{
    protected BaseRepository $baseRepository;

    public function __construct(BaseRepository $baseRepository)
    {
        $this->baseRepository = $baseRepository;
    }

    /**
     * @param array $payload
     * @return mixed
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws Error422Exception
     * @throws FileException
     * @throws InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws RepositoryReadOnlyException
     * @throws UpdateConstraintException
     * @throws XmlUtilException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function create(array $payload): mixed
    {
        // Get a primary key field name from mapper (supports composite keys as an array)
        $primaryKey = $this->baseRepository->getMapper()->getPrimaryKey();

        // Check if a primary key is provided in the payload
        foreach ($primaryKey as $pkField) {
            if (!empty($payload[$pkField])) {
                throw new Error422Exception(
                    "Create should not include primary key field: {$pkField}"
                );
            }
        }

        $model = $this->baseRepository->getMapper()->getEntity($payload);
        $this->baseRepository->save($model);
        return $model;
    }

    /**
     * @param array $payload
     * @return mixed
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws Error404Exception
     * @throws Error422Exception
     * @throws FileException
     * @throws InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws RepositoryReadOnlyException
     * @throws UpdateConstraintException
     * @throws XmlUtilException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function update(array $payload): mixed
    {
        // Get a primary key field name from mapper (supports composite keys as an array)
        $primaryKey = $this->baseRepository->getMapper()->getPrimaryKey();

        // Extract primary key value(s) from payload
        $pkValue = array_intersect_key($payload, array_flip($primaryKey));

        // Validate primary key is provided
        if (count($pkValue) !== count($primaryKey)) {
            $pkFieldNames = implode(', ', $primaryKey);
            throw new Error422Exception(
                "Update requires primary key field(s): {$pkFieldNames}"
            );
        }

        $model = $this->getOrFail($pkValue);
        ObjectCopy::copy($payload, $model);
        $this->baseRepository->save($model);
        return $model;
    }

    /**
     * @param array|string|int|LiteralInterface $id
     * @return mixed
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws InvalidArgumentException
     * @throws OrmInvalidFieldsException
     * @throws XmlUtilException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(array|string|int|LiteralInterface $id): mixed
    {
        return $this->baseRepository->get($id);
    }

    /**
     * @param array|string|int|LiteralInterface $id
     * @return mixed
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws Error404Exception
     * @throws FileException
     * @throws InvalidArgumentException
     * @throws OrmInvalidFieldsException
     * @throws XmlUtilException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getOrFail(array|string|int|LiteralInterface $id): mixed
    {
        $result = $this->baseRepository->get($id);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        return $result;
    }

    /**
     * @param int|null $page
     * @param int|null $size
     * @return array
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws InvalidArgumentException
     * @throws XmlUtilException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function list(?int $page = 0, ?int $size = 20): array
    {
        return $this->baseRepository->list($page ?? 0, $size ?? 20);
    }

    public function save(mixed $model): void
    {
        $this->baseRepository->save($model);
    }

    public function delete($id): void
    {
        $this->baseRepository->delete($id);
    }
}
