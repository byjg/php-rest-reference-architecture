<?php

namespace RestTemplate\Repository;

use RestTemplate\Psr11;
use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Exception\OrmModelInvalidException;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Repository;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use RestTemplate\Model\DummyHex;

class DummyHexRepository extends BaseRepository
{
    /**
     * DummyRepository constructor.
     *
     * @param DbDriverInterface $dbDriver
     *
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws InvalidArgumentException
     * @throws KeyNotFoundException
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws OrmModelInvalidException
     * @throws ReflectionException
     */
    public function __construct(DbDriverInterface $dbDriver)
    {
        $mapper = new Mapper(
            DummyHex::class,
            'dummyhex',
            'id',
            Psr11::container()->raw("_CLOSURE_NEWKEY")
        );

        Psr11::container()->get('_CLOSURE_FIELDMAP_ID', $mapper);
        $mapper->addFieldMap('uuid', 'uuid', Mapper::doNotUpdateClosure());

        $this->repository = new Repository($dbDriver, $mapper);
    }
}
