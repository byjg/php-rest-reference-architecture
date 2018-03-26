<?php

namespace RestTemplate\Repository;

use ByJG\MicroOrm\Query;
use RestTemplate\Psr11;

class DummyRepository extends BaseRepository
{
    /**
     * DummyRepository constructor.
     *
     * @throws \ByJG\Config\Exception\ConfigNotFoundException
     * @throws \ByJG\Config\Exception\EnvironmentException
     * @throws \ByJG\Config\Exception\KeyNotFoundException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct()
    {
        $this->repository = Psr11::container()->get('DUMMY_TABLE');
    }

    /**
     * @param $field string
     * @return null|\RestTemplate\Model\Dummy[]
     */
    public function getByField($field)
    {
        $query = Query::getInstance()
            ->table('dummy')
            ->where('dummy.field like :field', ['field' => "%$field%"]);

        $result = $this->repository->getByQuery($query);
        if (is_null($result)) {
            return null;
        }

        return $result;
    }
}
