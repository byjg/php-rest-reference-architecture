<?php

namespace RestTemplate\Repository;

use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\EnvironmentException;
use ByJG\Config\Exception\KeyNotFoundException;
use Builder\Psr11;
use Psr\SimpleCache\InvalidArgumentException;

class DummyHexRepository extends BaseRepository
{
    /**
     * DummyRepository constructor.
     *
     * @throws ConfigNotFoundException
     * @throws EnvironmentException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->repository = Psr11::container()->get('DUMMYHEX_TABLE');
    }
}
