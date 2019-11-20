<?php

namespace RestTemplate\Repository;

use Builder\Psr11;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Query;
use Psr\SimpleCache\InvalidArgumentException;
use RestTemplate\Model\Dummy;
use RestTemplate\Psr11;

class DummyRepository extends BaseRepository
{
    /**
     * DummyRepository constructor.
     *
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->repository = Psr11::container()->get('DUMMY_TABLE');
    }

    /**
     * @param $field string
     * @return null|Dummy[]
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
