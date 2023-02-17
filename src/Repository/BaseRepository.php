<?php

namespace RestTemplate\Repository;

use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\MicroOrm\Literal;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use ByJG\Serializer\Exception\InvalidArgumentException;

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
        if (!($itemId instanceof Literal) && preg_match("/^\w{8}-?\w{4}-?\w{4}-?\w{4}-?\w{12}$/", $itemId)) {
            $itemId = new Literal("X'" . str_replace("-", "", $itemId) . "'");
        }
        return $this->repository->get($itemId);
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
    public function getAll($page = 0, $size = 20, $orderBy = null, $filter = null)
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

    public function model()
    {
        $class = $this->repository->getMapper()->getEntity();

        return new $class();
    }

    /**
     * @param $model
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws InvalidArgumentException
     */
    public function save($model)
    {
        return $this->repository->save($model);
    }
}
