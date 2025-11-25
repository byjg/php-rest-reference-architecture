<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\MicroOrm\Exception\InvalidArgumentException;
use ByJG\MicroOrm\Exception\OrmModelInvalidException;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use ReflectionException;
use RestReferenceArchitecture\Model\Dummy;

class DummyRepository extends BaseRepository
{
    /**
     * DummyRepository constructor.
     *
     * @param DatabaseExecutor $executor
     * @throws OrmModelInvalidException
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function __construct(DatabaseExecutor $executor)
    {
        $this->repository = new Repository($executor, Dummy::class);
    }


    /**
     * @param mixed $field
     * @return null|Dummy[]
     */
    public function getByField($field)
    {
        $query = Query::getInstance()
            ->table('dummy')
            ->where('dummy.field = :value', ['value' => $field]);
        return $this->repository->getByQuery($query);
    }

}
