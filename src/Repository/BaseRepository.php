<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Core\Exception\DatabaseException;
use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\AnyDataset\Db\Exception\DbDriverNotConnected;
use ByJG\Config\Config;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\Config\Exception\RunTimeException;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\MicroOrm\Exception\RepositoryReadOnlyException;
use ByJG\MicroOrm\Exception\UpdateConstraintException;
use ByJG\MicroOrm\Interface\QueryBuilderInterface;
use ByJG\MicroOrm\Interface\UpdateConstraintInterface;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use ByJG\MicroOrm\Literal\Literal;
use ByJG\MicroOrm\Literal\LiteralInterface;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use ByJG\Serializer\Exception\InvalidArgumentException;
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

abstract class BaseRepository
{
    /**
     * @var Repository
     */
    protected Repository $repository;

    /**
     * @param array|string|int|LiteralInterface $itemId
     * @return mixed
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws OrmInvalidFieldsException
     * @throws XmlUtilException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(array|string|int|LiteralInterface $itemId): mixed
    {
        return $this->repository->get($itemId);
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getMapper(): Mapper
    {
        return $this->repository->getMapper();
    }

    public function getExecutor(): DatabaseExecutor
    {
        return $this->repository->getExecutor();
    }

    /**
     * @throws RepositoryReadOnlyException
     */
    public function getExecutorWrite(): DatabaseExecutor
    {
        return $this->repository->getExecutorWrite();
    }

    /**
     * @throws XmlUtilException
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getByQuery(QueryBuilderInterface $query): array
    {
        $query->table($this->repository->getMapper()->getTable());
        return $this->repository->getByQuery($query);
    }

    /**
     * @param int $page
     * @param int $size
     * @param string|array|null $orderBy
     * @param array|null $filter
     * @return array
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws InvalidArgumentException
     * @throws XmlUtilException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function list(int $page = 0, int $size = 20, string|array|null $orderBy = null, ?array $filter = null): array
    {
        $query = $this->listQuery(page: $page, size: $size, orderBy: $orderBy, filter: $filter);

        return $this->repository
            ->getByQuery($query);
    }

    /**
     * @param string $tableName
     * @param array $fields
     * @param int $page
     * @param int $size
     * @param string|array|null $orderBy
     * @param array|null $filter
     * @return array
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws InvalidArgumentException
     * @throws XmlUtilException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function listGeneric(string $tableName, array $fields = [], int $page = 0, int $size = 20, string|array|null $orderBy = null, ?array $filter = null): array
    {
        $query = $this->listQuery($tableName, $fields, $page, $size, $orderBy, $filter);

        $sqlStatement = $query->build($this->repository->getExecutor()->getDriver());

        $iterator = $this->repository->getExecutor()->getIterator($sqlStatement);
        return $iterator->toArray();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     */
    public function listQuery(?string $tableName = null, array $fields = [], int $page = 0, int $size = 20, string|array|null $orderBy = null, ?array $filter = null): Query
    {
        if (empty($page)) {
            $page = 0;
        }

        if (empty($size)) {
            $size = 20;
        }

        $query = Query::getInstance()
            ->table($tableName ?? $this->repository->getMapper()->getTable())
            ->limit($page * $size, $size);

        if (!empty($fields)) {
            $query->fields($fields);
        }

        if (!empty($orderBy)) {
            if (!is_array($orderBy)) {
                $orderBy = [$orderBy];
            }
            $query->orderBy($orderBy);
        }

        foreach ((array) $filter as $item) {
            $query->where($item[0], $item[1]);
        }

        return $query;
    }

    public function model()
    {
        $class = $this->repository->getMapper()->getEntity();

        return new $class();
    }

    /**
     * @return string|null
     * @throws ConfigException
     * @throws ContainerExceptionInterface
     * @throws DependencyInjectionException
     * @throws KeyNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws RunTimeException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getUuid(): string|null
    {
        return HexUuidLiteral::getFormattedUuid(Config::get(DatabaseExecutor::class)->getScalar("SELECT upper(uuid())"));
    }

    /**
     * @param mixed $model
     * @param UpdateConstraintInterface|array|null $updateConstraint
     * @return mixed
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws RepositoryReadOnlyException
     * @throws UpdateConstraintException
     * @throws XmlUtilException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function save(mixed $model, UpdateConstraintInterface|array|null $updateConstraint = null): mixed
    {
        $model = $this->repository->save($model, $updateConstraint);

        $primaryKey = $this->repository->getMapper()->getPrimaryKey()[0];

        if ($model->{"get$primaryKey"}() instanceof Literal) {
            $model->{"set$primaryKey"}(HexUuidLiteral::create($model->{"get$primaryKey"}()));
        }

        return $model;
    }

    /**
     * @param array|string|int|LiteralInterface $pkId
     * @return bool
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws OrmInvalidFieldsException
     * @throws RepositoryReadOnlyException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     */
    public function delete(array|string|int|LiteralInterface $pkId): bool
    {
        return $this->repository->delete($pkId);
    }
}
