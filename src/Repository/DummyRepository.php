<?php

namespace RestTemplate\Repository;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use RestTemplate\Model\Dummy;

class DummyRepository extends BaseRepository
{
    /**
     * DummyRepository constructor.
     *
     * @param DbDriverInterface $dbDriver
     *
     */
    public function __construct(DbDriverInterface $dbDriver)
    {
        $mapper = new Mapper(
            Dummy::class,
            'dummy',
            'id'
        );
        // $mapper->withPrimaryKeySeedFunction(function () {
        //     return $this->getClosureNewUUID();
        // });


        // Table UUID Definition
        // $this->setClosureFixBinaryUUID($mapper);


        $this->repository = new Repository($dbDriver, $mapper);
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
        $result = $this->repository->getByQuery($query);
        return $result;
    }

}
