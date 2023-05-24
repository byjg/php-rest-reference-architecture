<?php

namespace RestTemplate\Repository;

use RestTemplate\Psr11;
use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\FieldMapping;
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
     * @param $field string
     * @return null|Dummy[]
     * @throws \ByJG\MicroOrm\Exception\InvalidArgumentException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
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
    }

}
