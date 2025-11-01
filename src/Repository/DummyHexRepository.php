<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\MicroOrm\Exception\InvalidArgumentException;
use ByJG\MicroOrm\Exception\OrmModelInvalidException;
use ByJG\MicroOrm\Repository;
use ReflectionException;
use RestReferenceArchitecture\Model\DummyHex;

class DummyHexRepository extends BaseRepository
{
    /**
     * DummyHexRepository constructor.
     *
     * @param DatabaseExecutor $executor
     * @throws OrmModelInvalidException
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function __construct(DatabaseExecutor $executor)
    {
        $this->repository = new Repository($executor, DummyHex::class);
    }


}
