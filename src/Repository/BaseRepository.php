<?php

namespace RestTemplate\Repository;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\MicroOrm\FieldMapping;
use ByJG\MicroOrm\Literal;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use ByJG\Serializer\Exception\InvalidArgumentException;
use RestTemplate\Psr11;
use RestTemplate\Util\HexUuidLiteral;

abstract class BaseRepository
{
    /**
     * @var Repository
     */
    protected $repository;

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

    protected function prepareUuidQuery($itemId)
    {
        $result = [];
        foreach ((array)$itemId as $item) {
            if (!($item instanceof Literal) && preg_match("/^\w{8}-?\w{4}-?\w{4}-?\w{4}-?\w{12}$/", $item)) {
                $result[] = new HexUuidLiteral($item);
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
     * @param null $orderBy
     * @param null $filter
     * @return array
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function list($page = 0, $size = 20, $orderBy = null, $filter = null)
    {
        if (empty($page)) {
            $page = 0;
        }

        if (empty($size)) {
            $size = 20;
        }

        $query = Query::getInstance()
            ->table($this->repository->getMapper()->getTable())
            ->limit($page*$size, $size);

        if (!empty($orderBy)) {
            if (!is_array($orderBy)) {
                $orderBy = [$orderBy];
            }
            $query->orderBy($orderBy);
        }

        foreach ((array)$filter as $item) {
            $query->where($item[0], $item[1]);
        }

        return $this->repository
            ->getByQuery($query);
    }

    public function listGeneric($tableName, $page = 0, $size = 20, $orderBy = null, $filter = null)
    {
        if (empty($page)) {
            $page = 0;
        }

        if (empty($size)) {
            $size = 20;
        }

        $query = Query::getInstance()
            ->table($tableName)
            ->limit($page*$size, $size);

        if (!empty($orderBy)) {
            if (!is_array($orderBy)) {
                $orderBy = [$orderBy];
            }
            $query->orderBy($orderBy);
        }

        foreach ((array)$filter as $item) {
            $query->where($item[0], $item[1]);
        }

        $object = $query->build($this->repository->getDbDriver());

        $iterator = $this->repository->getDbDriver()->getIterator($object["sql"], $object["params"]);
        return $iterator->toArray();
    }

    public function model()
    {
        $class = $this->repository->getMapper()->getEntity();

        return new $class();
    }

    protected function getClosureNewUUID()
    {
        return new Literal("X'" . $this->repository->getDbDriver()->getScalar("SELECT hex(uuid_to_bin(uuid()))") . "'");
    }

    public static function getUuid()
    {
        return Psr11::container()->get(DbDriverInterface::class)->getScalar("SELECT insert(insert(insert(insert(hex(uuid_to_bin(uuid())),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')");
    }

    /**
     * @param Mapper $mapper
     * @param string $pkFieldName
     * @param string $modelField
     * @return void
     */
    protected function setClosureFixBinaryUUID($mapper, $pkFieldName = 'id', $modelField = 'uuid')
    {
        $mapper->addFieldMapping(FieldMapping::create($pkFieldName)
            ->withUpdateFunction(function ($value, $instance) {
                if (empty($value)) {
                    return null;
                }
                if (!($value instanceof Literal)) {
                    $value = new HexUuidLiteral($value);
                }
                return $value;
            })
            ->withSelectFunction(function ($value, $instance) use ($modelField) {
                return str_replace('-', '', $instance->{'get' . $modelField}());
            })
        );
    }

    /**
     * @param $model
     * @return mixed
     * @throws InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     */
    public function save($model)
    {
        $model = $this->repository->save($model);

        $primaryKey = $this->repository->getMapper()->getPrimaryKey()[0];

        if ($model->{"get$primaryKey"}() instanceof Literal) {
            $model->{"set$primaryKey"}(HexUuidLiteral::getUuidFromLiteral($model->{"get$primaryKey"}()));
        }

        return $model;
    }
}
