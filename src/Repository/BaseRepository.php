<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\MicroOrm\FieldMapping;
use ByJG\MicroOrm\Literal;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use ByJG\MicroOrm\UpdateConstraint;
use ByJG\Serializer\Exception\InvalidArgumentException;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Util\HexUuidLiteral;

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
     * @throws InvalidArgumentException
     */
    public function get($itemId)
    {
        return $this->repository->get($this->prepareUuidQuery($itemId));
    }

    public function getMapper()
    {
        return $this->repository->getMapper();
    }

    protected function prepareUuidQuery($itemId)
    {
        $result = [];
        foreach ((array)$itemId as $item) {
            if ($item instanceof Literal) {
                $result[] = $item;
                continue;
            }
            $hydratedItem = preg_replace('/[^0-9A-F\-]/', '', $item);
            if (preg_match("/^\w{8}-?\w{4}-?\w{4}-?\w{4}-?\w{12}$/", $hydratedItem)) {
                $result[] = new HexUuidLiteral($hydratedItem);
            } else {
                $result[] = $item;
            }
        }

        if (count($result) == 1) {
            return $result[0];
        }
        return $result;
    }

    /**
     * @param int|null $page
     * @param int $size
     * @param string|array|null $orderBy
     * @param string|array|null $filter
     * @return array
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
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

        $object = $query->build($this->repository->getDbDriver());

        $iterator = $this->repository->getDbDriver()->getIterator($object["sql"], $object["params"]);
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

    public static function getClosureNewUUID(): \Closure
    {
        return function () {
            return new Literal("X'" . Psr11::container()->get(DbDriverInterface::class)->getScalar("SELECT hex(uuid_to_bin(uuid()))") . "'");
        };
    }

    public static function getUuid()
    {
        return Psr11::container()->get(DbDriverInterface::class)->getScalar("SELECT insert(insert(insert(insert(hex(uuid_to_bin(uuid())),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')");
    }

    /**
     * @param Mapper $mapper
     * @param string $binPropertyName
     * @param string $uuidStrPropertyName
     * @return FieldMapping
     */
    protected function setClosureFixBinaryUUID(?Mapper $mapper, $binPropertyName = 'id', $uuidStrPropertyName = 'uuid')
    {
        $fieldMapping = FieldMapping::create($binPropertyName)
            ->withUpdateFunction(function ($value, $instance) {
                if (empty($value)) {
                    return null;
                }
                if (!($value instanceof Literal)) {
                    $value = new HexUuidLiteral($value);
                }
                return $value;
            })
            ->withSelectFunction(function ($value, $instance) use ($binPropertyName, $uuidStrPropertyName) {
                if (!empty($uuidStrPropertyName)) {
                    $fieldValue = $instance->{'get' . $uuidStrPropertyName}();
                } else {
                    $fieldValue = HexUuidLiteral::getFormattedUuid($instance->{'get' . $binPropertyName}(), false);
                }
                if (is_null($fieldValue)) {
                    return null;
                }
                return $fieldValue;
            });

        if (!empty($mapper)) {
            $mapper->addFieldMapping($fieldMapping);
        }

        return $fieldMapping;
    }

    /**
     * @param $model
     * @return mixed
     * @throws InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     */
    public function save($model, ?UpdateConstraint $updateConstraint = null)
    {
        $model = $this->repository->save($model, $updateConstraint);

        $primaryKey = $this->repository->getMapper()->getPrimaryKey()[0];

        if ($model->{"get$primaryKey"}() instanceof Literal) {
            $model->{"set$primaryKey"}(HexUuidLiteral::getUuidFromLiteral($model->{"get$primaryKey"}()));
        }

        return $model;
    }
}
