<?php

namespace RestReferenceArchitecture\Repository;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\FieldMapping;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\MapperClosure;
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
        $mapper->addFieldMapping(FieldMapping::create('uuid')->withFieldName('uuid')->withUpdateFunction(MapperClosure::readonly()));

        $this->repository = new Repository($dbDriver, $mapper);
    }


}
