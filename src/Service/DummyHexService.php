<?php

namespace RestReferenceArchitecture\Service;

use RestReferenceArchitecture\Repository\DummyHexRepository;

class DummyHexService extends BaseService
{
    public function __construct(DummyHexRepository $repository)
    {
        parent::__construct($repository);
    }
}
