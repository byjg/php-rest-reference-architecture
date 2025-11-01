<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Core\Exception\DatabaseException;
use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\AnyDataset\Db\Exception\DbDriverNotConnected;
use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\MicroOrm\Exception\RepositoryReadOnlyException;
use ByJG\MicroOrm\Exception\UpdateConstraintException;
use ByJG\MicroOrm\FieldMapping;
use ByJG\MicroOrm\Interface\UpdateConstraintInterface;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use ByJG\MicroOrm\Literal\Literal;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use ByJG\Serializer\Exception\InvalidArgumentException;
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use Closure;
use ReflectionException;
use RestReferenceArchitecture\Psr11;

abstract class BaseRepository
{
    /**
     * @var Repository
     */
    protected Repository $repository;

    /**
     * @param $itemId
     * @return mixed
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     */
    public function get($itemId)
    {
        return $this->repository->get(HexUuidLiteral::create($itemId));
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getMapper()
    {
        return $this->repository->getMapper();
    }

    public function getExecutor()
    {
        return $this->repository->getExecutor();
    }

    public function getExecutorWrite()
    {
        return $this->repository->getExecutorWrite();
    }

    public function getByQuery($query)
    {
        $query->table($this->repository->getMapper()->getTable());
        return $this->repository->getByQuery($query);
    }

    /**
     * @param int $page
     * @param int $size
     * @param null $orderBy
     * @param null $filter
     * @return array
     * @throws DatabaseException
     * @throws DbDriverNotConnected
     * @throws FileException
     * @throws XmlUtilException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function list($page = 0, $size = 20, $orderBy = null, $filter = null)
    {
        $query = $this->listQuery(page: $page, size: $size, orderBy: $orderBy, filter: $filter);

        return $this->repository
            ->getByQuery($query);
    }

    /**
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function listGeneric($tableName, $fields = [], $page = 0, $size = 20, $orderBy = null, $filter = null)
    {
        $query = $this->listQuery($tableName, $fields, $page, $size, $orderBy, $filter);

        $sqlStatement = $query->build($this->repository->getExecutor()->getDriver());

        $iterator = $this->repository->getExecutor()->getIterator($sqlStatement);
        return $iterator->toArray();
    }

    public function listQuery($tableName = null, $fields = [], $page = 0, $size = 20, $orderBy = null, $filter = null): Query
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

    public static function getClosureNewUUID(): Closure
    {
        return function () {
            return new Literal("X'" . Psr11::get(DbDriverInterface::class)->getScalar("SELECT hex(uuid_to_bin(uuid()))") . "'");
        };
    }

    /**
     * @return mixed
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    public static function getUuid()
    {
        return Psr11::get(DatabaseExecutor::class)->getScalar("SELECT upper(uuid())");
    }

    /**
     * @param Mapper|null $mapper
     * @param string $binPropertyName
     * @param string $uuidStrPropertyName
     * @return FieldMapping
     */
    protected function setClosureFixBinaryUUID(?Mapper $mapper, $binPropertyName = 'id', $uuidStrPropertyName = 'uuid')
    {
        $fieldMapping = FieldMapping::create($binPropertyName)
            ->withUpdateFunction(
                function ($value, $instance) {
                    if (empty($value)) {
                        return null;
                    }
                    if (!($value instanceof Literal)) {
                        $value = new HexUuidLiteral($value);
                    }
                    return $value;
                }
            )
            ->withSelectFunction(
                function ($value, $instance) use ($binPropertyName, $uuidStrPropertyName) {
                    if (!empty($uuidStrPropertyName)) {
                        $fieldValue = $instance->{'get' . $uuidStrPropertyName}();
                    } else {
                        $itemValue = $instance->{'get' . $binPropertyName}();
                        $fieldValue = HexUuidLiteral::getFormattedUuid($itemValue, false, $itemValue);
                    }
                    if (is_null($fieldValue)) {
                        return null;
                    }
                    return $fieldValue;
                }
            );

        if (!empty($mapper)) {
            $mapper->addFieldMapping($fieldMapping);
        }

        return $fieldMapping;
    }

    /**
     * @param  $model
     * @param UpdateConstraintInterface|array|null $updateConstraint
     * @return mixed
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws RepositoryReadOnlyException
     * @throws UpdateConstraintException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     */
    public function save($model, UpdateConstraintInterface|array|null $updateConstraint = null): mixed
    {
        $model = $this->repository->save($model, $updateConstraint);

        $primaryKey = $this->repository->getMapper()->getPrimaryKey()[0];

        if ($model->{"get$primaryKey"}() instanceof Literal) {
            $model->{"set$primaryKey"}(HexUuidLiteral::create($model->{"get$primaryKey"}()));
        }

        return $model;
    }
}
