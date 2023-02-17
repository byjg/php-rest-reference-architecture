<?php

namespace RestTemplate\Repository;

use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\Authenticate\Definition\UserDefinition;
use ByJG\MicroOrm\Exception\InvalidArgumentException;
use ByJG\MicroOrm\Exception\OrmModelInvalidException;
use ByJG\MicroOrm\Mapper;
use ByJG\MicroOrm\Repository;

Class UserRepository extends BaseRepository
{
    /**
     * UserRepository constructor.
     * @param DbDriverInterface $dbDriver
     * @param UserDefinition|null $userTable
     * @throws InvalidArgumentException
     * @throws OrmModelInvalidException
     */
    public function __construct(
        DbDriverInterface $dbDriver,
        UserDefinition $userTable = null
    ) {
        if (empty($userTable)) {
            $userTable = new UserDefinition();
        }

        $userMapper = new Mapper(
            $userTable->model(),
            $userTable->table(),
            $userTable->getEmail()
        );

        $propertyDefinition = $userTable->toArray();

        foreach ($propertyDefinition as $property => $map) {
            $userMapper->addFieldMap(
                $property,
                $map,
                $userTable->getClosureForUpdate($property),
                $userTable->getClosureForSelect($property)
            );
        }

        $this->repository = new Repository($dbDriver, $userMapper);
    }
}
