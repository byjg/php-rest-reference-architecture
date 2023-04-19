<?php

namespace RestTemplate\Repository;

use RestTemplate\Psr11;
use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\FieldMapping;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use RestTemplate\Model\DummyHex;

class DummyHexRepository extends BaseRepository
{
    /**
     * DummyHexRepository constructor.
     *
     * @param DbDriverInterface $dbDriver
     *
     */
    public function __construct(DbDriverInterface $dbDriver)
    {
        $mapper = new Mapper(
            DummyHex::class,
            'dummyhex',
            'id'
        );
        $mapper->withPrimaryKeySeedFunction(function () {
            return $this->getClosureNewUUID();
        });


        $this->setClosureFixBinaryUUID($mapper);
        $mapper->addFieldMapping(FieldMapping::create('uuid')->withFieldName('uuid')->withUpdateFunction(Mapper::doNotUpdateClosure()));

        $this->repository = new Repository($dbDriver, $mapper);
    }


}
