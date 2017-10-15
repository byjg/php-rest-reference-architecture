<?php

namespace RestTemplate\Repository;

use ByJG\MicroOrm\Literal;
use ByJG\MicroOrm\Query;

abstract class BaseRepository
{
    /**
     * @var \ByJG\MicroOrm\Repository
     */
    protected $repository;

    /**
     * @param $objectId
     * @return mixed
     */
    public function get($objectId)
    {
        if (!($objectId instanceof Literal)) {
            $objectId = new Literal("X'$objectId'");
        }
        return $this->repository->get($objectId);
    }

    /**
     * @param int|null $page
     * @param int $size
     * @param null $orderBy
     * @param null $filter
     * @return array
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

    public function save($model)
    {
        $this->repository->save($model);
    }
}
