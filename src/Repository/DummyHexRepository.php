<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\FieldMapping;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Repository;
use RestReferenceArchitecture\Model\DummyHex;

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
        $mapper->withPrimaryKeySeedFunction(BaseRepository::getClosureNewUUID());


        $this->setClosureFixBinaryUUID($mapper);
        $mapper->addFieldMapping(FieldMapping::create('uuid')->withFieldName('uuid')->withUpdateFunction(Mapper::doNotUpdateClosure()));

        $this->repository = new Repository($dbDriver, $mapper);
    }


}
