<?php

namespace RestTemplate\Repository;

use ByJG\MicroOrm\Query;
use Framework\Psr11;

class DummyRepository extends BaseRepository
{
    public function __construct()
    {
        $this->repository = Psr11::container()->getClosure('DUMMY_TABLE', null);
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
