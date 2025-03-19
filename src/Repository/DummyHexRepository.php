<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\Exception\OrmModelInvalidException;
use ByJG\MicroOrm\Repository;
use ReflectionException;
use RestReferenceArchitecture\Model\DummyHex;

class DummyHexRepository extends BaseRepository
{
    /**
     * DummyHexRepository constructor.
     *
     * @param DbDriverInterface $dbDriver
     * @throws OrmModelInvalidException
     * @throws ReflectionException
     */
    public function __construct(DbDriverInterface $dbDriver)
    {
        $this->repository = new Repository($dbDriver, DummyHex::class);
    }


}
